<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('conference')
                ->setRequired(true),
            ImageField::new('photoFilename', 'Ajouter une photo')
                ->setBasePath('upload/images')
                ->setUploadDir('public/upload/images')
                ->setFormType(FileUploadType::class)
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->setTemplatePath('admin/admin_photocomment.html.twig')
                ->setTextAlign('center'),
            DateTimeField::new('createdAt', 'Date & Heure')
                ->setTextAlign('center'),
            TextField::new('author', 'Auteur')
                ->setTextAlign('center')
                ->setHelp('votre nom'),
            EmailField::new('email', 'Email')
                ->setTextAlign('center'),
            TextEditorField::new('text', 'Texte')
                ->setTextAlign('center'),
        ];
    }

}
