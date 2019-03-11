<?php

namespace BackendBundle\Traits;

trait PrePersistTrait{

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }
}
