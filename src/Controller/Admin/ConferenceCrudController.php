<?php

namespace App\Controller\Admin;

use App\Entity\Conference;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class ConferenceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Conference::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            BooleanField::new('isInternational', 'International')
                ->setTextAlign('center'),
            TextField::new('city', 'ville')
                ->setTextAlign('center'),
            TextField::new('year', 'Année')
                ->setTextAlign('center'),

        ];
    }
}
