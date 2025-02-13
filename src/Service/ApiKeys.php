<?php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ApiKeys
{
    private $publicKey;
    private $privateKey;
    private $cacert;

    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;

        $this->publicKey = $_ENV['COOL_PAY_PUBLIC_KEY'];
        $this->privateKey = $_ENV['COOL_PAY_PRIVATE_KEY'];
        $this->cacert = $_ENV['COOL_PAY_CACERT_KEY'];
    }

    public function getPublicKey()
    {
        $publicKeyPath = $this->parameterBag->get('kernel.project_dir') . '/config/coolPayKeys/public.pem';
        $publicKeyContent = file_get_contents($publicKeyPath);
        if ($publicKeyContent === false) {
            return ;
        }

        return  $publicKeyContent;
    }

    public function getAPIToken()
    {
        $publicKeyPath = $this->parameterBag->get('kernel.project_dir') . '/config/coolPayKeys/apiToken.pem';
        $publicKeyContent = file_get_contents($publicKeyPath);
        if ($publicKeyContent === false) {
            return ;
        }

        return  $publicKeyContent;
    }

    public function getPrivateKey()
    {
        $private = $this->parameterBag->get('kernel.project_dir') . '/config/coolPayKeys/private.pem';
        $privateContent = file_get_contents($private);
        if ($privateContent === false) {
            return ;
        }
        return $privateContent;
    }
    public function getCacert(){
        return $this->parameterBag->get('kernel.project_dir') . '/config/coolPayKeys/cacert.pem';

    }

    public function getPublicKeyContent()
    {
        //$publicKeyPath = $this->getParameter('kernel.project_dir') . '/config/coolPayKeys/public.pem';
        $publicKeyContent = file_get_contents($this->privateKey);

        if ($publicKeyContent === false) {
            return ;
        }

        return  $publicKeyContent;
    }
}
