-----------------------------------------------
nouveau projet
ouvrir visual code sur le dossier www de laragon
faire
symfony new nomduprojet --version x.x --full
reouvrir visual code dans le dossier monprojet
modifier .env
mettre le projet en fr
config->packages->translation.yaml mettre fr à la place de en
travailler avec un certificat ssl/tls avec https://
faire
symfony server:ca:{un}install
dans php.ini retirer le ; devant extension=openssl
composer config -g -- disable-tls false
symfony server:start
pour voir ce qui manque
symfony book:check-requirements
retirer le ; dans php.ini sur ce qui manque et installer ce qui manque à part docker


Mettre en place une protection de l'administration avec symfony 5.2.9 et easyadmin 3.2

1 - suivre https://symfony.com/doc/current/security.html

*attention : au départ le fichier config/packages/security.yaml

security:
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            anonymous: false
            lazy: true
    access_control:
        # - { path: ^/admin, roles: ROLE_ADMIN }
    
        # - { path: ^/profile, roles: ROLE_USER, ROLE_PREMIUM }

*faire
composer require symfony/security-bundle
*faire
php bin/console make:user
    repondre:
        User
        yes
        email
        yes
*faire
php bin/console make:migration
php bin/console doctrine:migrations:migrate
*voir modification de security.yaml
security:
    providers:
        # used to reload user from session & other features (e.g. switch_user)
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email
     encoders:
        App\Entity\User:
            algorithm: auto            
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            anonymous: false
            lazy: true
    access_control:
        # - { path: ^/admin, roles: ROLE_ADMIN }
    
        # - { path: ^/profile, roles: ROLE_USER, ROLE_PREMIUM }

*utilisation du bundle fixture pour placer un administrateur dans la table user
*faire
composer require doctrine/doctrine-fixtures-bundle (voir packagist)
php bin/console make:fixtures
    UserFixtures
le fichier UserFixtures.php est générer

*faire pour générer un password avec protection argon
php bin/console security:encode-password
copier le crypto Argon pour le mettre dans UserFixtures.php comme ci-dessous
*modifier le fichier src/DataFixtures/UserFixtures.php comme ceci:
<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail("ebacour78@gmail.com");
        $this->addReference('USER_ADMIN', $user); pour info addReference pour le champ JSON roles qui sera USER_ADMIN
        $user->setPassword('$argon2id$v=19$m=65536,p=1$bjJ6Lk5ZOW1yYjZWL1BlMA$BFdl0QnG4s15jZIr1GIYycnKbwfL3qSzLJAxk34DsmY');
        $manager->persist($user);
        $manager->flush();
    }
}
ATTENTION TRES IMPORTANT SAUVEGARDER LES TABLES DEJA RENSEIGNEES DANS LA BDD avec phpmyadmin
*faire ensuite
php bin/console doctrine:fixtures:load
cette fonction va éffacer le contenu des tables de la BDD et vas écrire le l'utilisateur configuré dans la table user.
*réinjecter vos tables sauvegardées dans la BDD

*ensuite cliquer sur How to build Form dans la doc
*faire pour créer un formulaire d'authentifacation
php bin/console make:auth
    1 ([1] Login form authenticator)
    Loginxxx (ATTENTION sans Authenticaror au bout, sinon on a un fichier qui resemble à LoginxxxAuthenticarorAuthenticaror.php)
    SecurityController
    yes
*symfony créé 4 fhichiers
*voir modification de security.yaml
security:
    providers:
        # used to reload user from session & other features (e.g. switch_user)
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email
     encoders:
        App\Entity\User:
            algorithm: auto            
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            anonymous: false
            lazy: true
        logout:
                path: app_logout
                # retour page d'accueil
                target: /home ------------------->ATTENTION rajouter cette ligne pour un retour à la homepage quand on logout sinon on se retrouve sur l'interface symfony 127.0.0.1:8000
        guard:
                authenticators:
                    - App\Security\LoginAmlAuthenticator    
    access_control:
        # - { path: ^/admin, roles: ROLE_ADMIN }
        # - { path: ^/profile, roles: ROLE_USER, ROLE_PREMIUM }

*faire dans src/Security/LoginxxxAuthenticator.php
a la fin du fichier dans function onAuthenticationSuccess
     //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
     // redirect to some "app_homepage" route - of wherever you want
     return new RedirectResponse($this->urlGenerator->generate('app_homepage'));

*crée un login admin dans base.html.twig avec la route {{ 'app_login' }}
------------------
créer le dashboard easyadmin:
php bin/console make:admin:dashboard

montrer les administrateur et leur droit ds easyadmin 3.2
*créer le UsercrudController.php
php bin/console make:admin:crud
    choisir le n° App/Entity/User suivre le reste
dans src/Controller/Admin/UserCrudController.php
*modifier avec ce qui suit:
<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('email', 'Email')
                ->setTextAlign('center'),
            ArrayField::new('roles', 'Role')
                ->setTextAlign('center'),
            TextField::new('password', 'mot de passe crypté'),    
        ];
    }  
}
----------------
créer les autres champs de la table User
php bin/console make:entity
User
rajouter le nom.....
---------------
créer un formulaire de création de compte user
php bin/console make:registration-form
cela va créer 4 fichiers:
templates/registration/confirmation_email.html.twig
src/Form/RegistrationFormType.php
src/Controller/RegistrationController.php
templates/registration/register.html.twig
suivre les recommendations sur ce site
https://rojas.io/symfony-4-login-registration-system/
pour le champs isVerified des la table user, je pense qu'il faut faire:
php bin/console doctrine:migrations:diff 
php bin/console doctrine:migrations:migrate
composer require debug --dev
bien rajouter form_themes: ['bootstrap_4_layout.html.twig'] ds config/packages/twig.yaml
ensuite peux jouer avec bootstrap pour améliorer la vue du formulaire
par contre ds LoginUserAuthenticator.php; a la fin ne pas mettre 
return new RedirectResponse($this->urlGenerator->generate('main'));
mais
return new RedirectResponse($this->urlGenerator->generate('home'));

-------------------------------------------------
crée un crud en front:
voir https://www.comment-devenir-developpeur.com/symfony-5/creer-un-crud-avec-symfony-et-doctrine/

-----------------------------------------------
travailler avec un certificat ssl/tls
faire
symfony server:ca:install
dans php.ini retirer le ; devant extension=openssl
composer config -g -- disable-tls false
symfony server:start
pour voir ce qui manque
symfony book:check-requirements
retirer le ; dans php.ini sur ce qui manque et installer ce qui manque à part docker



---------exemple du crudcarouselcontroller-------------
<?php

namespace App\Controller\Admin;

use App\Entity\Carouselhome;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CarouselhomeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Carouselhome::class;
    }
    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('rank', 'Le n° de Rang')
                ->setTextAlign('center'),
            BooleanField::new('active', 'Slide actif'),
            ImageField::new('imageFilename', 'Slide')
            ->setBasePath('upload/images')
            ->setUploadDir('public/upload/images')
            ->setFormType(FileUploadType::class)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false)
            ->setTemplatePath('admin/admin_imagecarouselhome.html.twig'),
            TextField::new('titre', 'Le titre dans le carousel')
                ->setTextAlign('center'),
            TextField::new('texte', 'Le texte dans le carousel')
                ->setTextAlign('center'),
        ];
    } 
}
-------------rendu des images----------------
ImageField
templates/admin/admin_imagecarouselhome.html.twig recupéré dans vendor/easycorp\easyadmin-bundle/Resources/views/crud/field/image.html.twig et renomé

{# @var ea \EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext #}
{# @var field \EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto #}
{# @var entity \EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto #}
{% set lightbox_content_id = 'ea-lightbox-' ~ field.uniqueId %}
<a href="#" class="ea-lightbox-thumbnail" data-lightbox-content-selector="#{{ lightbox_content_id }}">
    <img src="{{ asset(field.formattedValue) }}" class="img-fluid">
</a>
<div id="{{ lightbox_content_id }}" class="ea-lightbox">
    <img  src="{{ asset(field.formattedValue) }}">
</div>

*pour finir modifier le fichier d'easyadmin3.2 src/Controller/admin/DashbordController.php
<?php

namespace App\Controller\Admin;

use App\Entity\Carouselhome;
use App\Controller\Admin\UserCrudController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        // redirect to some CRUD controller
        $routeBuilder = $this->get(AdminUrlGenerator::class);
        return $this->redirect($routeBuilder->setController(UserCrudController::class)->generateUrl());
        //return parent::index();
    }
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('AML-PC Development');
    }
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Administration', 'fa fa-home');
        yield MenuItem::linkToCrud('Le Carousel Homepage', 'far fa-images', Carouselhome::class);
    }
}
----------------------POUR CREER LE GITHUB-------------
sur le site github:
faire new repository en cliquant sur le + en haut à droite
donner un nom au repository(dépôt) ex amlpc
une description : ex AML-PC DEVLOPMENT
Cliquez sur le bouton vert create en bas
ensuite ouvrir le terminal git tout en étant dans son projet symfony
faire:
git remote add origin https://github.com/albior78/amlpc.git (recopier la ligne donnée et rajouter-> git remote add origin .....)
ensuite faire:
git add --all
git commit -m 'ajout des fichiers au dépôt'
git push -u origin master
bingo le projet est sur github
aller sur gitKrakken
----------------------------------------------------------------
mise en ligne sur site mutualisé
ex: hostinger
faire 
télécharger les fichiers sur public_html

créer 2 .htaccess : 

1 dans public_html avec les commandes ci-dessous:

RewriteEngine on
RewriteCond %{HTTP_HOST} ^(www.)?VOTRESITE.com$
RewriteCond %{REQUEST_URI} !^/public/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /public/$1
RewriteCond %{HTTP_HOST} ^(www.)?VOTRESITE.com$
RewriteRule ^(/)?$ public/index.php [L]

1 dans le dossier public de symfony avec les commandes suivantes:

<IfModule mod_rewrite.c>
    Options -MultiViews
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

<IfModule !mod_rewrite.c>
    <IfModule mod_alias.c>
        RedirectMatch 302 ^/$ /index.php/
    </IfModule>
</IfModule>

mettre APP_ENV=prod à la place de dev dans .env
mettre également à jours les paramettres sqlsever

le 26/06/2021
Attention: si l'on veut incorporer directement le jquery dans le twig pour récupérer le loop.index par exemple, il faut déclarer le jquery en-dessous de {% block body %}<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script> et faire son script en dessous l'image qui comporte loop.index class=".....{{ 'slyle' ~ loop.index }}", faire le jquery entre <script type="text/javascript"> .... </script>

le 30/06/2021
installation d'un captcha (botdetect captcha)
composer require captcha-com/symfony-captcha-bundle:"4.*"
modifier config/routes.yaml avec:
captcha_routing:
  resource: "@CaptchaBundle/Resources/config/routing.yml"
vérifier dans config/bundles.php
Captcha\Bundle\CaptchaBundle\CaptchaBundle::class => ['all' => true],
Dans config/packages/ créer le fichier captcha.php avec dedans:
<?php 
if (!class_exists('CaptchaConfiguration')) 
{ 
return;
}
// BotDetect PHP Captcha configuration options
return [
  // Captcha configuration for example page
  'ExampleCaptcha' => [
    'UserInputID' => 'captchaCode',
    'ImageWidth' => 250,
    'ImageHeight' => 50,
  ],
];
Dans l'entité User rajouter:

    use Captcha\Bundle\CaptchaBundle\Validator\Constraints as CaptchaAssert;

    /**
    * @CaptchaAssert\ValidCaptcha(
    *      message = "CAPTCHA validation failed, try again."
    * )
    */
    protected $captchaCode;

    public function getCaptchaCode()
    {
        return $this->captchaCode;
    }

    public function setCaptchaCode($captchaCode)
    {
        $this->captchaCode = $captchaCode;
    }

Dans le formulaire d'enregistrement rajouter:

    use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;

            ->add('captchaCode', CaptchaType::class, [
                'label' => 'Vérification',
                'captchaConfig' => 'ExampleCaptcha'
            ])

Dans le template rajouter à l'endroit voulu: exemple:
<div class="font-weight-bold">{{ form_row(registrationForm.captchaCode) }}</div>