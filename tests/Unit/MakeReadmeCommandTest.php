<?php

use SchenkeIo\LaravelGa4Marketing\Workbench\App\Console\Commands\MakeReadmeCommand;
use SchenkeIo\PackagingTools\Markdown\MarkdownAssembler;

it('calls the assembler with correct parameters', function () {
    $assembler = Mockery::mock(MarkdownAssembler::class);
    $assembler->shouldReceive('addMarkdown')->with('introduction.md')->once();
    $assembler->shouldReceive('addTableOfContents')->once();
    $assembler->shouldReceive('addMarkdown')->with('installation.md')->once();
    $assembler->shouldReceive('addMarkdown')->with('usage.md')->once();
    $assembler->shouldReceive('writeMarkdown')->with('README.md')->once();

    /** @var MakeReadmeCommand $command */
    $command = Mockery::mock(MakeReadmeCommand::class)->makePartial();
    $command->shouldAllowMockingProtectedMethods();
    $command->shouldReceive('getAssembler')->andReturn($assembler);
    $command->shouldReceive('info')->andReturnNull();

    $status = $command->handle();

    expect($status)->toBe(0);
});

it('returns failure status if an exception occurs', function () {
    $assembler = Mockery::mock(MarkdownAssembler::class);
    $assembler->shouldReceive('addMarkdown')->andThrow(new Exception('test error'));

    /** @var MakeReadmeCommand $command */
    $command = Mockery::mock(MakeReadmeCommand::class)->makePartial();
    $command->shouldAllowMockingProtectedMethods();
    $command->shouldReceive('getAssembler')->andReturn($assembler);
    $command->shouldReceive('info')->andReturnNull();
    $command->shouldReceive('error')->andReturnNull();

    $status = $command->handle();

    expect($status)->toBe(1);
});
