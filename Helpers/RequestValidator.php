<?php

/**
 * Clase de ayuda simple para la validación de datos de entrada.
 * Se puede expandir con más reglas (email, numeric, min, max, etc.).
 */
class RequestValidator
{
    private array $errors = [];
    private array $input;

    public function __construct(array $input)
    {
        $this->input = $input;
    }

    /**
     * Valida el input contra un conjunto de reglas.
     * @param array $rules ['nombre_campo' => 'required|email']
     */
    public function validate(array $rules): void
    {
        foreach ($rules as $field => $ruleSet) {
            $rulesForField = explode('|', $ruleSet);
            foreach ($rulesForField as $rule) {
                $value = $this->input[$field] ?? null;

                if ($rule === 'required' && (is_null($value) || $value === '')) {
                    $this->addError($field, "El campo '$field' es requerido.");
                }

                // Aquí se pueden añadir más reglas de validación.
                // Ejemplo:
                // if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                //     $this->addError($field, "El campo '$field' debe ser un email válido.");
                // }
            }
        }
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}