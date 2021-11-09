<?php

namespace Yepwoo\Laragine\Processors;

class FactoryProcessor extends Processor
{
    /**
     * type str
     *
     * @var string
     */
    private string $type_str;

    /**
     * modifier str
     *
     * @var string
     */
    private string $mod_str;


    /**
     * start processing
     */
    public function process(): array
    {
        foreach ($this->json['attributes'] as $column => $cases) {
            $this->type_str = '';
            $this->mod_str = '';

            $this->processors['factory_str'] .= <<<STR
                                    '$column' => \$this->faker->
                        STR;

            if(isset($cases['mod'])) {
              $this->handleModCase($cases['mod']);
            }

            $this->handleTypeCase($cases['type'], $column);

            /**
             * Check if type_str is empty or not
             */

            $this->processors['factory_str']   .= ($this->mod_str !== '' ? $this->mod_str . '->': '') . ($this->type_str !== '' ? $this->type_str : 'text()');
            $this->processors['factory_str']  .= array_key_last($this->json['attributes']) == $column ? ',' : ",\n";
        }

        return $this->processors;
    }

    /**
     * handle modifier case
     *
     * @param $mod
     * @return void
     */
    private function handleModCase($mod): void
    {
        $modifiers        = explode("|", strtolower($mod));
        $schema_modifiers = $this->schema['definitions'];

        foreach ($modifiers as $modifier) {
          if($schema_modifiers[$modifier] && $schema_modifiers[$modifier]['factory'] !== '') {
              $this->mod_str .= $schema_modifiers[$modifier]['factory'] . '()';
          }
        }
    }

    /**
     * handle type case
     *
     * @param $type
     * @return void
     */
    private function handleTypeCase($type, $column_name): void
    {
        $type = strtolower($type);

        $schema_types = $this->schema['types'];
        if($schema_types[$type]) {
            if($schema_types[$type]['factory'] !== "" && !is_array($schema_types[$type]['factory'])) {
                $type = $schema_types[$type]['factory'] .'()' ;
                $this->mod_str .= $type !== '' ? $type . '()': '';
            }
            else if(is_array($schema_types[$type]['factory'])) { // have special cases
                $special_cases = $schema_types[$type]['factory'];
                foreach ($special_cases as $case => $value) { // ex: 'email' => safeEmail
                    if(strpos($column_name, $case)) {
                        $this->type_str .= $value . '()';
                    }
                }

                if($this->type_str == '') {
                    $this->type_str .= $special_cases['default'] . '(100)';
                }
            }
        }
    }
}
