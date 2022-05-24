<?php

namespace simialbi\yii2\ticket\migrations;

use Yii;
use yii\db\Migration;

class m220524_095500_rbac_updates extends Migration
{
    /**
     * {@inheritDoc}
     * @throws \yii\base\Exception
     * @throws \Exception
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        if ($auth) {
            $rule = $auth->getRule('isAssigned');

            // Create new permission viewAssignedTicket and add to viewTicket and ticketAuthor
            $viewAssignedTicket = $auth->createPermission('viewAssignedTicket');
            $viewAssignedTicket->description = 'View assigned ticket';
            $viewAssignedTicket->ruleName = $rule->name;
            $auth->add($viewAssignedTicket);

            $viewTicket = $auth->getPermission('viewTicket');
            $author = $auth->getRole('ticketAuthor');

            $auth->addChild($viewAssignedTicket, $viewTicket);
            $auth->addChild($author, $viewAssignedTicket);


            // Add permission update ticket to ticketAdministrator
            $updateTicket = $auth->getPermission('updateTicket');
            $administrator = $auth->getRole('ticketAdministrator');
            $auth->addChild($administrator, $updateTicket);


            // Add permission administrateTicket to Role ticketAuthor
            $administrateTicket = $auth->getPermission('administrateTicket');
            $auth->addChild($author, $administrateTicket);


            // Move permission assignTicket from administrator to agent
            $agent = $auth->getRole('ticketAgent');
            $assignTicket = $auth->getPermission('assignTicket');
            $auth->removeChild($administrator, $assignTicket);
            $auth->addChild($agent, $assignTicket);


            // Add permission closeTicket to ticketAdministrator
            $closeTicket = $auth->getPermission('closeTicket');
            $auth->addChild($administrator, $closeTicket);
        }
    }

    /**
     * {@inheritDoc}
     * @throws \yii\base\Exception
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        if ($auth) {
            // delete viewAssignedTicket
            $viewAssignedTicket = $auth->getPermission('viewAssignedTicket');
            $auth->remove($viewAssignedTicket);

            // remove permission update ticket from ticketAdministrator
            $updateTicket = $auth->getPermission('updateTicket');
            $administrator = $auth->getRole('ticketAdministrator');
            $auth->removeChild($administrator, $updateTicket);

            // remove permission administrateTicket from Role ticketAuthor
            $author = $auth->getRole('ticketAuthor');
            $administrateTicket = $auth->getPermission('administrateTicket');
            $auth->removeChild($author, $administrateTicket);

            // move permission assignTicket from agent to administrator
            $agent = $auth->getRole('ticketAgent');
            $assignTicket = $auth->getPermission('assignTicket');
            $auth->removeChild($agent, $assignTicket);
            $auth->addChild($administrator, $assignTicket);

            // Remove permission closeTicket from ticketAdministrator
            $closeTicket = $auth->getPermission('closeTicket');
            $auth->removeChild($administrator, $closeTicket);
        }
    }
}
