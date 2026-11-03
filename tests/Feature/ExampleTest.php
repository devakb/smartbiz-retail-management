<?php

test('the login page is reachable', function () {
    $this->get(route('login'))->assertOk();
});
