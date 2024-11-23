<?php

namespace App\User\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\MailService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
//use Symfony\Component\Mailer\MailerInterface;
use App\User\Service\UserService;
use App\User\DTO\Request\RequestResetPasswordDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\User\DTO\Response\ResponseResetPasswordDTO;
use App\Service\Utility\ProjectEmailAddressProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

final class ResetPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        //private readonly MailerInterface $mailer,
        private readonly MailService $mailService,
        private readonly ProjectEmailAddressProvider $emailAddressProvider,
    ) {}

    /**
     * @param RequestResetPasswordDTO $data
     * @throws TransportExceptionInterface
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $email = mb_strtolower($data->email);
        $user = $this->userService->getUserByEmail($email);

        if (!$user) {
            throw new NotFoundHttpException('Not registered');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface) {
            throw new BadRequestException('Too many reset password requests');
        }

        $context = [
            'resetToken'            => $resetToken,
            'name'                  => $user->getPrintName(),
            'token'                 => $resetToken->getToken(),
            'expirationMessageKey'  => $resetToken->getExpirationMessageKey(),
            'expirationMessageData' => $resetToken->getExpirationMessageData(),
        ];

        $email = (new TemplatedEmail())
            ->from($this->emailAddressProvider->provide())
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context($context)
        ;

        $this->mailService->sendResetPassword($user, $context);
        //$this->mailer->send($email);

        return new JsonResponse(
            data: new ResponseResetPasswordDTO(
                message: "Email sent"
            ),
            status: Response::HTTP_OK
        );
    }
}
