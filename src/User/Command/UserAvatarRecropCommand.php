<?php

namespace App\User\Command;

use App\Service\Infrastructure\ImageService;
use App\Service\S3Service;
use App\Service\Utility\ImageHelper;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use App\User\Service\UserService;
use \Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:avatar-recrop',
    description: 'Re-crop users avatars',
)]
class UserAvatarRecropCommand extends Command
{
    public function __construct(
        private string         $publicDir,
        private ImageService   $imageService,
        private UserService    $userService,
        private S3Service      $s3Service,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('uid', InputArgument::OPTIONAL, 'User uid')
            ->addOption('approved', null, InputOption::VALUE_OPTIONAL, 'approved avatar?', true)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $output->writeln('Start');

        $uid = $input->getArgument('uid');
        $approved = $input->getOption('approved');
        $approved = $approved === 'false' ? false : (bool)$approved;
        $output->writeln('Avatar approved: '. ($approved ? 'yes' : 'no') );

        $users = $uid
            ? [$this->userService->getUserByUid($uid, false)]
            : $this->userRepository->findAll();

        $output->writeln('Count users: '.count($users));

        $userAvatarDir = UserService::AVATAR_DIR;
        $userAvatarDirFullPath = sprintf('%s/%s', $this->publicDir, $userAvatarDir);
        if (!file_exists($userAvatarDirFullPath)) {
            mkdir($userAvatarDirFullPath, 0777, true);
        }

        foreach ($users as $user) {
            try {
                $output->writeln(sprintf('User %s - %s - %s', $user->getEmail(), $user->getId(), $user->getUid()));
                $output->writeln('Old avatar: '.$user->getAvatar());
                if (!$user->getAvatar()) {
                    continue;
                }

                $rawUrl = $user->getAvatar();
                if ($originalUrl = $user->getAvatarData()->getOriginalUrl()) {
                    $rawUrl = $originalUrl;
                }

                try {
                    $rawFile = ImageHelper::fileGetContents($rawUrl);
                } catch (Exception $e) {
                    $io->error(sprintf('Failed to get contents from url - %s. Error: %s',
                        $rawUrl,
                        $e->getMessage()));
                    continue;
                }

                try {
                    $rawJpg = ImageHelper::convertImageToJpg($rawUrl);
                } catch (Exception $e) {
                    $io->error(sprintf('Failed to convert to jpg from url - %s. Error: %s',
                        $rawUrl,
                        $e->getMessage()));
                    continue;
                }

                $jpgPublicPath = sprintf('%s/%s', $userAvatarDir, ImageHelper::generateFileName('jpg'));
                $jpgPublicPathFull = sprintf('%s/%s', $this->publicDir, $jpgPublicPath);

                $cropPublicPath = sprintf('%s/%s', $userAvatarDir, ImageHelper::generateFileName('jpg'));
                $cropPublicPathFull = sprintf('%s/%s', $this->publicDir, $cropPublicPath);

                $blurPublicPath = sprintf('%s/%s', $userAvatarDir, ImageHelper::generateFileName('jpg'));
                $blurPublicPathFull = sprintf('%s/%s', $this->publicDir, $blurPublicPath);

                file_put_contents($jpgPublicPathFull, $rawJpg);
                ImageHelper::correctImageOrientation($jpgPublicPathFull);

                $this->imageService->cropImage($jpgPublicPath, $cropPublicPathFull, ImageService::THUMB_AVATAR);
                $this->imageService->createBlur($cropPublicPathFull, $blurPublicPathFull);

                $output->writeln(sprintf('Image jpg: %s', $jpgPublicPath));
                $output->writeln(sprintf('Image crop: %s', $cropPublicPath));
                $output->writeln(sprintf('Image blur: %s', $blurPublicPath));

                $cdnJpg = $this->s3Service->uploadContentToCDN(
                    ImageHelper::fileGetContents($jpgPublicPathFull),
                    $jpgPublicPath,
                    true
                );
                $cdnCrop = $this->s3Service->uploadContentToCDN(
                    ImageHelper::fileGetContents($cropPublicPathFull),
                    $cropPublicPath,
                    true
                );
                $cdnBlur = $this->s3Service->uploadContentToCDN(
                    ImageHelper::fileGetContents($blurPublicPathFull),
                    $blurPublicPath,
                    true
                );

                $output->writeln(sprintf('CDN jpg: %s', $cdnJpg));
                $output->writeln(sprintf('CDN crop: %s', $cdnCrop));
                $output->writeln(sprintf('CDN blur: %s', $cdnBlur));

                unlink($jpgPublicPathFull);
                unlink($cropPublicPathFull);
                unlink($blurPublicPathFull);

                $user
                    ->setAvatar($approved ? $cropPublicPath : $blurPublicPath)
                    ->setAvatarStatus($approved ? User::AVATAR_STATUS_ACTIVE : User::AVATAR_STATUS_WAITING_APPROVE);
                $user->getAvatarData()
                    ->setOriginal($jpgPublicPath)
                    ->setCrop($cropPublicPath)
                    ->setCropBlur($blurPublicPath);
                $this->userService->saveUser($user);

                $output->writeln('New avatar url: '.$user->getAvatarUrl());
                $output->writeln('');
            } catch (Exception $e) {
                $io->error('Failed to re-crop user avatar. Error: '.$e->getMessage());
            }
        }

        $output->writeln('Finish');

        return Command::SUCCESS;
    }
}
