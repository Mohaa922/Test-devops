<?php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class RecaptchaService 
{
    public function __construct(private readonly HttpClientInterface $client, private readonly ParameterBagInterface $param)
    {    
    }

    public function verify(Request $request): bool
    {
        $recaptcha = $this->client->request('POST','https://www.google.com/recaptcha/api/siteverify', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'body' => [
                'secret' => $this->param->get('app.recaptcha.privatekey'),
                'response' => $request->request->get('g-recaptcha-response')
            ]
        ]);
        if(!$recaptcha->toArray()['success']) {
            return false;
        }
        return true;
    }
}