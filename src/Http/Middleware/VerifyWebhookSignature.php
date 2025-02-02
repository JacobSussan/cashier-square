<?php

namespace Laravel\Cashier\Http\Middleware;

use Closure;
use Square\Exceptions\ApiException;
use Square\WebhookSignature;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyWebhookSignature
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function handle($request, Closure $next)
    {
        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                $request->header('Square-Signature'),
                config('cashier.webhook.secret'),
                config('cashier.webhook.tolerance')
            );
        } catch (ApiException $exception) {
            throw new AccessDeniedHttpException('Invalid webhook signature', $exception);
        }

        return $next($request);
    }
}
