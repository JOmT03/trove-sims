<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    private function ownerOnly(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'Owner', 403, 'Owner only.');
    }

    public function downloadBackup()
    {
        $this->ownerOnly();

        $sql  = $this->buildSqlDump();
        $base = 'trove_backup_' . date('Y-m-d_His');

        if (class_exists('\ZipArchive')) {
            $zipPath = storage_path('app/' . $base . '.zip');
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFromString('database.sql', $sql);
                $imgDir = storage_path('app/public/products');
                if (is_dir($imgDir)) {
                    foreach (glob($imgDir . DIRECTORY_SEPARATOR . '*') as $f) {
                        if (is_file($f)) {
                            $zip->addFile($f, 'photos/' . basename($f));
                        }
                    }
                }
                $zip->close();
                return response()->download($zipPath, $base . '.zip')->deleteFileAfterSend(true);
            }
        }

        // Fallback: plain .sql
        return response($sql, 200, [
            'Content-Type'        => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $base . '.sql"',
        ]);
    }

    private function buildSqlDump(): string
    {
        $pdo = DB::getPdo();
        $out  = "-- Trove backup " . date('Y-m-d H:i:s') . "\n";
        $out .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = [];
        foreach (DB::select('SHOW TABLES') as $row) {
            $vals = array_values((array) $row);
            $tables[] = $vals[0];
        }

        foreach ($tables as $table) {
            $createRow = DB::select('SHOW CREATE TABLE `' . $table . '`');
            $createArr = (array) $createRow[0];
            $createSql = $createArr['Create Table'] ?? ($createArr['Create View'] ?? '');
            if ($createSql === '') { continue; }

            $out .= "DROP TABLE IF EXISTS `" . $table . "`;\n";
            $out .= $createSql . ";\n\n";

            $rows = DB::select('SELECT * FROM `' . $table . '`');
            foreach ($rows as $r) {
                $r    = (array) $r;
                $cols = array_map(function ($c) { return '`' . $c . '`'; }, array_keys($r));
                $vals = array_map(function ($v) use ($pdo) {
                    if (is_null($v)) return 'NULL';
                    return $pdo->quote((string) $v);
                }, array_values($r));
                $out .= "INSERT INTO `" . $table . "` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
            }
            $out .= "\n";
        }

        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $out;
    }

    public function restoreBackup(Request $request)
    {
        $this->ownerOnly();
        $request->validate(['backup' => 'required|file']);

        $file = $request->file('backup');
        $ext  = strtolower($file->getClientOriginalExtension());
        $sql  = null;

        try {
            if ($ext === 'zip' && class_exists('\ZipArchive')) {
                $zip = new \ZipArchive();
                if ($zip->open($file->getRealPath()) === true) {
                    $sql = $zip->getFromName('database.sql');
                    $zip->close();
                }
                if ($sql === false || $sql === null) {
                    return back()->with('error', 'Could not find database.sql inside the backup zip.');
                }
            } else {
                $sql = file_get_contents($file->getRealPath());
            }

            DB::unprepared($sql);
            return back()->with('success', 'Backup restored successfully. You may need to sign in again.');
        } catch (\Exception $e) {
            \Log::error('Restore failed: ' . $e->getMessage());
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }
}