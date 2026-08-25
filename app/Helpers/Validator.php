<?php
namespace App\Helpers;

use App\Core\Database;

class Validator {
    private $errors = [];

    public function validate($data, $rules) {
        foreach ($rules as $field => $fieldRules) {
            $value = isset($data[$field]) ? trim($data[$field]) : '';

            foreach ($fieldRules as $rule) {
                $parts = explode(':', $rule);
                $ruleName = $parts[0];
                $ruleValue = $parts[1] ?? null;

                switch ($ruleName) {
                    case 'required':
                        if (empty($value) && $value !== '0') {
                            $this->errors[$field][] = "Le champ " . htmlspecialchars($field, ENT_QUOTES, 'UTF-8') . " est obligatoire.";
                        }
                        break;
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$field][] = "L'adresse email n'est pas valide.";
                        }
                        break;
                    case 'min':
                        if (!empty($value) && strlen($value) < intval($ruleValue)) {
                            $this->errors[$field][] = "Le champ doit faire au moins {$ruleValue} caractères.";
                        }
                        break;
                    case 'max':
                        if (!empty($value) && strlen($value) > intval($ruleValue)) {
                            $this->errors[$field][] = "Le champ ne doit pas dépasser {$ruleValue} caractères.";
                        }
                        break;
                    case 'unique':
                        if (!empty($value) && $ruleValue) {
                            $args = explode(',', $ruleValue);
                            $table = $args[0];
                            $column = $args[1];
                            $excludeId = $args[2] ?? null;

                            $db = Database::getInstance();
                            if ($excludeId) {
                                $stmt = $db->query("SELECT COUNT(*) AS count FROM `{$table}` WHERE `{$column}` = ? AND id != ?", [$value, $excludeId]);
                            } else {
                                $stmt = $db->query("SELECT COUNT(*) AS count FROM `{$table}` WHERE `{$column}` = ?", [$value]);
                            }
                            $res = $stmt->fetch();
                            if ($res && $res['count'] > 0) {
                                $this->errors[$field][] = "Cette valeur est déjà utilisée.";
                            }
                        }
                        break;
                }
            }
        }
        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }
}
