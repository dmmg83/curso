<?php

namespace BackendBundle\Traits;

/**
 * trait para incluir fecha de creación
 */
trait FechaCreacion
{
    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \Datetime("now");
    }

}
