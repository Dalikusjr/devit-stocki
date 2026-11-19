-- Maintenance : le passage au mois suivant est géré par l'application (MaintainService::renewIfDue),
-- qui fait aussi avancer la date d'échéance. L'ancien événement MySQL remettait seulement le statut
-- à « non payé » sans changer la date, et dépendait du planificateur d'événements MySQL.
DROP EVENT IF EXISTS initStatusMaintain;
