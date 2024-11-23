<?php

namespace App\Service;

use App\Entity\Mail\Mail;
use App\Entity\Mail\MailType;
use App\Exception\Http\RunTimeError\RunTimeHttpException;
use App\Repository\MailRepository;
use App\Security\Domain\AuthUserInterface;
use App\Service\Infrastructure\LogService;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\User\Entity\User;

class MailService
{
    public function __construct(
        private readonly RedisService   $redisService,
        private readonly MailRepository $mailRepository,
        private readonly LogService     $logger,
    ) {
    }

    private function createMail(
        string $email,
        string $type,
        array  $context = [],
    ): Mail {
        $mail = new Mail();
        $mail
            ->setEmailTo($email)
            ->setType($type)
            ->setContext($context);

        return $this->mailRepository->save($mail);
    }

    private function addMailToQueue(Mail $mail): Mail
    {
        try {
            $this->redisService->pushToQueue(RedisKeys::QUEUE_MAIL,
                serialize([
                    'id' => $mail->getId(),
                ]));
        } catch (\Throwable $e) {
            $this->logger->error('Failed to add mail to queue',
                [
                    'mail_id' => $mail->getId(),
                    'error'   => $e->getMessage(),
                ]);

            $mail
                ->setStatus(Mail::STATUS_ERROR)
                ->setError($e->getMessage());
            $this->mailRepository->save($mail);

            throw new RunTimeHttpException('Failed to add mail to queue');
        }

        $mail->setStatus(Mail::STATUS_READY);

        return $this->mailRepository->save($mail);
    }

    public function sendConfirmationRegistration(
        AuthUserInterface $user,
        array             $context,
    ): Mail {
        $mail = $this->createMail(
            email: $user->getEmail(),
            type: MailType::CONFIRMATION_REGISTRATION,
            context: $context,
        );

        return $this->addMailToQueue($mail);
    }

    public function sendResetPassword(
        User  $user,
        array $context,
    ): Mail {
        $mail = $this->createMail(
            email: $user->getEmail(),
            type: MailType::RESET_PASSWORD,
            context: $context,
        );

        return $this->addMailToQueue($mail);
    }

    public function getById(int $id): ?Mail
    {
        return $this->mailRepository->findOneBy(['id' => $id]);
    }
}
