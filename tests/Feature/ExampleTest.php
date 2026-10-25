<?php

it('sends guests to the login page', function () {
    $this->get('/')->assertRedirect('/login');
    $this->get('/login')->assertOk();
});
