<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to hack each of these messages to make
    | them your own.
    |
    */

    'accepted' => 'El :attribute debe ser aceptado.',
    'active_url' => 'El :attribute no es una URL válida.',
    'after' => 'El :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => 'El :attribute solo puede contener letras.',
    'alpha_dash' => 'El :attribute solo puede contener letras, números y guiones.',
    'alpha_num' => 'El :attribute solo puede contener letras y números.',
    'array' => 'El :attribute debe ser un arreglo.',
    'at' => 'El :attribute debe ser :date.',
    'before' => 'El :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'numeric' => 'El :attribute debe estar entre :min y :max.',
        'file' => 'El :attribute debe estar entre :min y :max kilobytes.',
        'string' => 'El :attribute debe estar entre :min y :max caracteres.',
        'array' => 'El :attribute debe tener entre :min y :max elementos.',
    ],
    'boolean' => 'El :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación del :attribute no coincide.',
    'date' => 'El :attribute no es una fecha válida.',
    'date_equals' => 'El :attribute debe ser igual a :date.',
    'date_format' => 'El :attribute no coincide con el formato :format.',
    'different' => 'El :attribute y :other deben ser diferentes.',
    'digits' => 'El :attribute debe tener :digits dígitos.',
    'digits_between' => 'El :attribute debe tener entre :min y :max dígitos.',
    'dimensions' => 'El :attribute tiene unas dimensiones de imagen no permitidas.',
    'distinct' => 'El campo :attribute tiene un valor duplicado.',
    'email' => 'El :attribute debe ser un correo electrónico válido.',
    'ending_with' => 'El :attribute debe terminar con :value.',
    'exists' => 'El :attribute seleccionado no existe.',
    'file' => 'El :attribute debe ser un archivo.',
    'filled' => 'El campo :attribute es obligatorio.',
    'gt' => [
        'numeric' => 'El :attribute debe ser mayor que :value.',
        'file' => 'El :attribute debe ser mayor que :value kilobytes.',
        'string' => 'El :attribute debe ser mayor que :value caracteres.',
        'array' => 'El :attribute debe tener más de :value elementos.',
    ],
    'gte' => [
        'numeric' => 'El :attribute debe ser mayor o igual a :value.',
        'file' => 'El :attribute debe ser mayor o igual a :value kilobytes.',
        'string' => 'El :attribute debe ser mayor o igual a :value caracteres.',
        'array' => 'El :attribute debe tener :value o más elementos.',
    ],
    'image' => 'El :attribute debe ser una imagen.',
    'in' => 'El :attribute seleccionado no es válido.',
    'in_array' => 'El :attribute no existe en :other.',
    'integer' => 'El :attribute debe ser un número entero.',
    'ip' => 'El :attribute debe ser una dirección IP válida.',
    'ipv4' => 'El :attribute debe ser una dirección IPv4 válida.',
    'ipv6' => 'El :attribute debe ser una dirección IPv6 válida.',
    'json' => 'El :attribute debe ser una cadena JSON válida.',
    'lt' => [
        'numeric' => 'El :attribute debe ser menor que :value.',
        'file' => 'El :attribute debe ser menor que :value kilobytes.',
        'string' => 'El :attribute debe ser menor que :value caracteres.',
        'array' => 'El :attribute debe tener menos de :value elementos.',
    ],
    'lte' => [
        'numeric' => 'El :attribute debe ser menor o igual a :value.',
        'file' => 'El :attribute debe ser menor o igual a :value kilobytes.',
        'string' => 'El :attribute debe ser menor o igual a :value caracteres.',
        'array' => 'El :attribute debe tener :value o menos elementos.',
    ],
    'max' => [
        'numeric' => 'El :attribute no debe ser mayor que :max.',
        'file' => 'El :attribute no debe ser mayor que :max kilobytes.',
        'string' => 'El :attribute no debe ser mayor que :max caracteres.',
        'array' => 'El :attribute no debe tener más de :max elementos.',
    ],
    'mimes' => 'El :attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => 'El :attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'numeric' => 'El :attribute no debe ser menor que :min.',
        'file' => 'El :attribute no debe ser menor que :min kilobytes.',
        'string' => 'El :attribute no debe ser menor que :min caracteres.',
        'array' => 'El :attribute no debe tener menos de :min elementos.',
    ],
    'not_in' => 'El :attribute seleccionado no es válido.',
    'not_regex' => 'El formato del :attribute es no válido.',
    'numeric' => 'El :attribute debe ser un número.',
    'password' => 'La contraseña proporcionada es incorrecta.',
    'regex' => 'El formato del :attribute es no válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless' => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando esté presente :values.',
    'required_with_all' => 'El campo :attribute es obligatorio cuando estén presentes :values.',
    'required_without' => 'El campo :attribute es obligatorio cuando no esté presente alguno de :values.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values esté presente.',
    'same' => 'El :attribute y :other deben ser iguales.',
    'size' => [
        'numeric' => 'El :attribute debe ser :size.',
        'file' => 'El :attribute debe ser :size kilobytes.',
        'string' => 'El :attribute debe tener :size caracteres.',
        'array' => 'El :attribute debe contener :size elementos.',
    ],
    'starts_with' => 'El :attribute debe comenzar con :value.',
    'string' => 'El :attribute debe ser una cadena de texto.',
    'timezone' => 'El :attribute debe ser una zona válida.',
    'unique' => 'El :attribute ya ha sido tomado.',
    'uploaded' => 'El :attribute no se pudo subir.',
    'url' => 'El :attribute debe ser una URL válida.',
    'uuid' => 'El :attribute debe ser un UUID válido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute placeholders with
    | something more reader friendly such as E-Mail Address instead of "email".
    |
    */

    'attributes' => [],

];
