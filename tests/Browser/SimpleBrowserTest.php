<?php

it('can visit simple', function () {
    $page = visit('/')->withLocale('fr-FR');
    $page->assertSee('Workbench Test Page');
});
