<?php

namespace App\Command;

use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:s3:get:all',
    description: 'Get S3 videos',
)]
class S3GetBucketCommand extends Command
{
    public function __construct(
        private readonly S3Client $s3Client,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /*$result = $this->s3Client->listObjects([
            'Bucket' => 'vcdev',
        ]);*/

        $result = $this->s3Client->getObject([
            'Bucket' => getenv('AWS_S3_BUCKET'),
            'Key' => '367430696_986706255944743_6493045183147652341_n.jpeg',
        ]);

        /** @var Stream $body */
        $body = $result->get('Body');
        $body->rewind();
        file_put_contents('~/test.jpeg', $body->getContents()) ;

        return Command::SUCCESS;
    }
}
