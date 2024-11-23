<?php

namespace App\Command\Tech;

use App\Entity\Settings\Country;
use App\Service\S3Service;
use App\Service\Utility\DomainHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(
    name: 'app:tech:flags-upload-cdn',
    description: 'Upload countries flags to CDN',
)]
class FlagsUploadCDNCommand extends Command
{
    public function __construct(
        private S3Service $s3Service,
        private readonly KernelInterface $kernel,
    )
    {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addOption('upload', null, InputOption::VALUE_OPTIONAL, 'Upload file to cdn')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $needUpload = $input->getOption('upload');

        $path = 'countries-flags';
        $dir = sprintf('%s/public/%s', $this->kernel->getProjectDir(), $path);
        $io->writeln($dir);
        $io->writeln('CDN: '.DomainHelper::getCdnDomain());

        $countries = Country::COUNTRIES;

        $files = scandir($dir);

        $cdnList = [];
        $countriesNotOnList = [];

        foreach ($files as $fileName) {
            if (in_array($fileName, ['.', '..'])) {
                continue;
            }

            [$country, $ext] = explode('.',$fileName);

            $file = sprintf('%s/%s', $dir, $fileName);
            $newPath = trim(sprintf('%s/%s', $path, $fileName), '/');

            $cdnPath = sprintf('%s/%s', DomainHelper::getCdnDomain(), $newPath);
            if (isset($countries[$country]) || $country==='default') {
                $io->writeln(sprintf('"%s" => "%s",', $country, $newPath));

                $fileObject = new File($file);
                if ($needUpload) {
                    $resultPath = $this->s3Service->uploadFileToCDN(
                        file: $fileObject,
                        newFilePath: $newPath,
                        options: [
                            'params' => [
                                'ContentType' => 'image/svg+xml',
                            ],
                        ],
                    );
                }
                $cdnList[]=$cdnPath;

                if ($country!=='default') {
                    unset($countries[$country]);
                }
            } else {
                $countriesNotOnList[]=$country;
            }
        }

        if (count($cdnList)) {
            $io->writeln('CDN list:');
            foreach ($cdnList as $country) {
                $io->writeln(sprintf('- %s', $country));
            }
        }

        if (count($countriesNotOnList)) {
            $io->writeln('Country not on our list:');
            foreach ($countriesNotOnList as $country) {
                $io->writeln(sprintf('- %s', $country));
            }
        }

        if (count($countries)) {
            $io->writeln('our countries that are not on the list:');
            foreach ($countries as $country => $title) {
                $io->writeln(sprintf('- %s', $country));
            }
        }

        return Command::SUCCESS;
    }
}
