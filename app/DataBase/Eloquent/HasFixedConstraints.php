<?php
namespace App\DataBase\Eloquent;

use Illuminate\Database\Eloquent\Model;

trait HasFixedConstraints
{
    protected $saveConstraints = [];

    public function withFixedConstraint($attribute, $value, $with_selecting = true)
    {
        $this->saveConstraints[$attribute] = $value;
        if($with_selecting)
            $this->query->where($attribute, $value);
        return $this;
    }

    protected function setFixedConstraints(Model $model)
    {
        foreach ($this->saveConstraints as $attribute => $value) {
            if (is_null($model->getAttribute($attribute))) {
                $model->setAttribute($attribute, $value);
            }
        }
    }
}
