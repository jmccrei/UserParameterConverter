User Parameter Converter
--

Piggybacks off Sensio ParamConverter

## Installation

composer.json

```
"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/jmccrei/UserParameterConverter.git"
        }
    ]
```

then in cmd: `composer require jmccrei/user-param-converter:"dev-main as 1.0-stable"
`

### Usage

On controller route
```php
    // of course replace App\Entity\Entity with your actual entity class

    // BASIC - Single entity
   /**
    * ...
    * @ParamConverter("entity", options={ "user_bind" = true } )
    * OR EXPLICITLY
    * @ParamConverter("entities", options={
    *   "user_bind"={
    *        "enabled"=true,
    *        "type"="single",
    *        "entity"=Entity::class
    *        }
    *   })
    * ...
    **/
    public function someAction( App\Entity\Entity $entity ) {
        ...
    }

    // COLLECTION
    /**
     * ...
     * @ParamConverter("entities", options={
     *   "user_bind"={
     *        "enabled"=true,
     *        "type"="collection",
     *        "entity"=Entity::class
     *        }
     *   })
     * ...
     **/
    public function someAction( ArrayCollection $entities ) {
        ...
    }
```
