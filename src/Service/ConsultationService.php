<?php

namespace App\Service;

use App\Domain\Service\ConsultationServiceInterface;

class ConsultationService implements ConsultationServiceInterface
{
    public function getConsultationById(string $idConsultation, Array $params = []) : Array
    {
        
    }

    public function getAllConsultations(Array $params = []) : Array
    {

    }

    public function getPieces(string $idConsultation) : Array
    {

    }

    public function setAvis(string $idConsultation, bool $est_favorable = true, array $prescriptions = [], array $documents = [], ?\DateTime $date_envoi) : void
    {

    }

    public function setPEC(string $idConsultation, bool $est_positive = true, \DateInterval $date_limite_reponse_interval = null, string $observations = null, array $documents = [], ?\DateTime $date_envoi) : void
    {

    }

}