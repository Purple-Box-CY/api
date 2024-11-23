<?php

namespace App\Command\Tech;

use App\Service\S3Service;
use App\Service\Utility\DomainHelper;
use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(
    name: 'app:tech:upload-file-cdn',
    description: 'Upload file to CDN',
)]
//Example: php bin/console app:tech:upload-file-cdn https://d3v8wzla6n4b2q.cloudfront.net/uploads/preview/01HVGPMMTG9KDS29X67PFE9NS7/d9d500be1f0598b36cc0ba693223c5cb.webp og-image-logo.jpg --content-type="image/jpeg"
class UploadFileCDNCommand extends Command
{
    private Client $client;

    public function __construct(
        private S3Service $s3Service,
    )
    {
        parent::__construct();
        $this->client = new Client();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('from', InputArgument::REQUIRED, 'File link from')
            ->addArgument('to', InputArgument::REQUIRED, 'New path file')
            ->addOption('content-type', null, InputOption::VALUE_OPTIONAL, 'Content type')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $from = $input->getArgument('from');
        $to = $input->getArgument('to');

        $contentType = $input->getOption('content-type');

        $io->writeln('CDN: '.DomainHelper::getCdnDomain());

        $tmpFilePath = '/tmp/tempcdnfile';
        try {
            $this->client->get($from, ['sink' => $tmpFilePath]);
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        $file = new File($tmpFilePath);

        $options = [];
        if ($contentType) {
            $options['params'] = [
                'ContentType' => $contentType,
            ];
        }

        $resultPath = $this->s3Service->uploadFileToCDN(
            file: $file,
            newFilePath: $to,
            options: $options,
        );

        $io->writeln('RESULT: '.$resultPath);

        unlink($tmpFilePath);

        return Command::SUCCESS;
    }
}
