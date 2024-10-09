<?php

namespace Agnes\ItexmoSms;

class ItexmoSmsServiceProvider
{
    protected $itexmoSms;

    public function __construct(array $config)
    {
        // Validate the config
        if (!isset($config['api_code'])) {
            throw new \InvalidArgumentException('API code is required.');
        }

        // Instantiate the ItexmoSms class
        $this->itexmoSms = new ItexmoSms($config);
    }

    public function getItexmoSms()
    {
        return $this->itexmoSms;
    }

    public function publishConfig($filePath)
    {
        $configContent = <<<'PHP'
<?php

return [
    'api_code' => 'your_api_code',
];
PHP;

        file_put_contents($filePath, $configContent);
    }
}
