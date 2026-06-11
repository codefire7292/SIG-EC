<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'Administrateur technique';
    case MAIRE = 'Maire ou Délégué';
    case OFFICIER = "Officier d'état-civil";
    case SUPERVISEUR = 'Superviseur / Chef de centre';
    case AGENT = "Agent d'état-civil";

    public function label(): string
    {
        return $this->value;
    }
}
