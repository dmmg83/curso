<?php

namespace BackendBundle\Traits;

trait PreUpdateTrait{

    /**
     * @ORM\PreUpdate
     */
    public function setUpdateAtValue()
    {
        $this->updatedAt = new \DateTime();
    }
}
