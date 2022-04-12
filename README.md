# Ticket system for yii2


## Resources

## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require --prefer-dist simialbi/yii2-ticket
```

or add

```
"simialbi/yii2-ticket": "^1.0.0"
```

to the `require` section of your `composer.json`.

## Usage

In order to use this module, you will need to:

1. [Setup Module](#setup-module) your application so that the module is available.
2. [Create a user identity](#create-identity) class which extends UserInterface

### Setup Module
Configure the module in the modules section of your Yii configuration file.

```php
'modules' => [
    'ticket' => [
        'class' => 'simialbi\yii2\ticket\Module',
        //'richTextFields' => true,
        //'kanbanModule' => 'kanban',
        //'smsProvider' => 'smsProvider',
        //'on ticketCreated' => function ($event) {},
        //[...]
    ]
]
```

#### Parameters

| Parameter        | Description                                                                                                                                             |
|------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------|
| `richTextFields` | Set this parameter to true to use rich text fields. To do this you need to add `simialbi/yii2-summernote` to the require section of your composer.json. |
| `kanbanModule`   | If you use the [Kanban Module](https://github.com/simialbi/yii2-kanban) too, you can put the id of the module here to use link functionality.           |
| `smsProvider`    | If you wan't to add SMS notification functionality, insert the sms component id here. It must implement `\simialbi\yii2\sms\ProviderInterface`.         |

#### Events
| Event                    | Description                                                |
|--------------------------|------------------------------------------------------------|
| `EVENT_TICKET_CREATED`   | Will be triggered after a new ticket was created.          |
| `EVENT_TICKET_UPDATED`   | Will be triggered after a ticket was updated.              |
| `EVENT_TICKET_ASSIGNED`  | Will be triggered after a ticket was assigned to an agent. |
| `EVENT_TICKET_RESOLVED`  | Will be triggered after a ticket was resolved.             |
| `EVENT_TICKET_COMMENTED` | Will be triggered after a ticket received a new comment.   |

### Setup console config and apply migrations

Apply the migrations either with the following command: `yii migrate --migration-namespaces='simialbi\yii2\ticket\migrations'`
or configure your console like this:

```php
[
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => [
                'simialbi\yii2\ticket\migrations'
            ]
        ]
    ]
]
```

and apply the `yii migrate` command.

> Be sure to have an authManager configured. The migration process creates the needed roles by this module.

### Create identity

Create an identity class which implements `simialbi\yii2\models\UserInterface` e.g.:
```php
<?php
use yii\db\ActiveRecord;
use simialbi\yii2\models\UserInterface;

class User extends ActiveRecord implements UserInterface
{
    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritDoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritDoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritDoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * {@inheritDoc}
     */
    public function getName() {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * {@inheritDoc}
     */
    public function getMobile() {
        return $this->mobile;
    }

    /**
     * {@inheritDoc}
     */
    public static function findIdentities() {
        return static::find()->all();
    }
}
```

After creating this class define it as identity class in your application configuration:
```php
'components' => [
    'user' => [
        'identityClass' => 'app\models\User'
    ]
]
``` 

### Assign roles to users

The migration process created the needed roles and permissions by this module. You should now have the following roles
created:

| Role                  | Description                                                                                                                                                          |
|-----------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `ticketAuthor`        | Users with this role assigned can create new tickets, update and close their own tickets and create new comments in their own tickets.                               |
| `ticketAgent`         | Users with this role assigned can take tickets in assigned topics, create comments in tickets they have taken and resolve tickets.                                   |
| `ticketAdministrator` | Users with this role assigned can administrate topics, take tickets in all topics, create comments in all tickets, assign tickets to agents and resolve all tickets. |

Assign the roles to the corresponding users. Afterwards you can navigate to `/ticket/topic` to administrate your ticket
topics (e.g. `IT`). Each user with a role assigned can navigate to `/ticket` to view either their own tickets 
(`ticketAuthor`), tickets of the topics they were assigned (`ticketAgent`) or all tickets of all topics 
(`ticketAdministrator`). 

## License

**yii2-ticket** is released under MIT license. See bundled [LICENSE](LICENSE) for details.

## Acknowledgments
