<?php

namespace SchenkeIo\LaravelGa4Marketing\Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use SchenkeIo\PackagingTools\Markdown\MarkdownAssembler;

class MakeReadmeCommand extends Command
{
    protected $signature = 'workbench:make-readme';

    protected $description = 'Generate README.md from fragments';

    public function handle(): int
    {
        $this->info('Generating README.md...');

        try {
            $assembler = $this->getAssembler('workbench/resources/md');
            $assembler->addMarkdown('introduction.md');
            $assembler->addTableOfContents();
            $assembler->addMarkdown('installation.md');
            $assembler->addMarkdown('usage.md');
            $assembler->writeMarkdown('README.md');
            $this->info('README.md generated successfully.');
        } catch (\Exception $e) {
            $this->error('Error generating README.md: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function getAssembler(string $path): MarkdownAssembler
    {
        return new MarkdownAssembler($path);
    }
}
