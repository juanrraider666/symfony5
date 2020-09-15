<?php


namespace App\Upload;

use Doctrine\Common\Collections\Collection;
use Manuel\Bundle\UploadDataBundle\Builder\ValidationBuilder;
use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Mapper\ColumnsMapper;

class UploadCardConfig extends UploadConfig
{
    public function configureColumns(ColumnsMapper $mapper, array $options){

    }

    public function configureValidations(ValidationBuilder $builder, array $options){

    }

     public function transfer(Upload $upload, Collection $items){

    }
}