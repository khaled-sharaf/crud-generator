<?php

namespace Khaled\CrudSystem\Services;

class CrudConfigTransformService
{

    /**
     * Convert the config model to form
     * 
     * @param array $configModel
     * @return array
     */
    public function convertConfigToForm(array $configModel): array
    {
        return $configModel;
    }

    /**
     * Convert the config form to model
     * 
     * @param array $configForm
     * @return array
     */
    public function convertConfigToModel(array $configForm): array
    {
        return $configForm;
    }

}