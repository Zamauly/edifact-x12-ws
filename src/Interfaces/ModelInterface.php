<?php
namespace App\Interfaces;
use mysqli;

interface ModelInterface{

    public function setId(int $id);

    public function getId() : int;

    public function setCreatedAt(string $createdAt);

    public function getCreatedAt() : string;

    public function setActive(bool $active);

    public function getActive() : bool;

    public function __toString() : string;

    // CRUD OPERATIONS
    public function create(array $data = []);

    public function read(int $id = 0);

    public function update(int $id = 0, array $data = []);

    public function delete(int $id = 0);
}
