<?php

namespace Aurora\App;

interface ModuleInterface
{
    public function add(array $data): string|bool;

    public function save(int $id, array $data): bool;

    public function checkFields(array $data, $id = null): array;

    public function getCondition(array $search): string;
}
