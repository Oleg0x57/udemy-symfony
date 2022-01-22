<?php

namespace App\Entity\Verb;

use App\Factory\Verb as VerbFactory;
use Doctrine\ORM\EntityManagerInterface;

class VerbManager
{
    /** EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createVerb(Infinitivo $infinitivo)
    {
        $this->entityManager->persist($infinitivo);
        $this->entityManager->flush();
        
        $this->generateFormsForInfinitivo($infinitivo);
    }

    public function generateFormsForInfinitivo(Infinitivo $infinitivo)
    {
        $verbTitle = $infinitivo->getTitle();

        if ($infinitivo->hasModoIndicativo()) {
            throw new \RuntimeException('Verb ' . $verbTitle . ' already has Modo Indicativo form, use regenerate button');
        }

        if ($infinitivo->hasPreterioSimple()) {
            throw new \RuntimeException('Verb ' . $verbTitle . ' already has Preterio Simple form, use regenerate button');
        }

        if ($infinitivo->hasFuturoSimple()) {
            throw new \RuntimeException('Verb ' . $verbTitle . ' already has Futuro Simple form, use regenerate button');
        }

        if ($infinitivo->hasFuturoProximo()) {
            throw new \RuntimeException('Verb ' . $verbTitle . ' already has Futuro Proximo form, use regenerate button');
        }

        if ($infinitivo->getIsRegular()) {
            $ending = substr($verbTitle, -2);
            $wordRoot = substr($verbTitle, 0, -2);

            switch ($ending) {
                case 'ar':
                    $factory = new VerbFactory\ArVerbFormFactory($verbTitle, $wordRoot);
                    break;
                case 'er':
                    $factory = new VerbFactory\ErVerbFormFactory($verbTitle, $wordRoot);
                    break;
                case 'ir':
                    $factory = new VerbFactory\IrVerbFormFactory($verbTitle, $wordRoot);
                    break;

            }

            $modoIndicativo = $factory->createModoIndicativo();
            $preterioSimple = $factory->createPreterioSimple();
            $futuroSimple = $factory->createFuturoSimple();
            $futuroProximo = $factory->createFuturoProximo();

            $modoIndicativo->setInfinitivo($infinitivo);
            $preterioSimple->setInfinitivo($infinitivo);
            $futuroSimple->setInfinitivo($infinitivo);
            $futuroProximo->setInfinitivo($infinitivo);

            $this->entityManager->persist($modoIndicativo);
            $this->entityManager->persist($preterioSimple);
            $this->entityManager->persist($futuroSimple);
            $this->entityManager->persist($futuroProximo);

            $this->entityManager->flush();
        } else {
            throw new \RuntimeException('Verb ' . $verbTitle . ' is irregular, use manul create forms');
        }
    }

    public function deleteAllFormsForInfinitivo(Infinitivo $infinitivo)
    {
        foreach ($infinitivo->getTimeForms() as $verbForm) {
            $this->entityManager->remove($verbForm);
        }

        $this->entityManager->flush();
    }

    public function regenerateFormsForInfinitivo(Infinitivo $infinitivo)
    {
        $this->deleteAllFormsForInfinitivo($infinitivo);
        $this->generateFormsForInfinitivo($infinitivo);
    }

    public function deleteVerb(Infinitivo $infinitivo)
    {
        $this->deleteAllFormsForInfinitivo($infinitivo);
        $this->entityManager->remove($infinitivo);
        $this->entityManager->flush();
    }
}
