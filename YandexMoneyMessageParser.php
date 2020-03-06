class YandexMoneyMessageParser {

    private $wallet;
    private $amount;
    private $code;
    private $response;

    /**
     * @param $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Run parser
     *
     * On input receives a line from service Yandex.Money and returns:
     *      - amount
     *      - wallet
     *      - code
     *
     *
     * @return array
     * @throws \Exception
     */
    public function parse()
    {
        if (!isset($this->response)) {
            throw new \Exception('Response empty, please set response');
        }

        $this->parseAmount();
        $this->parseWalletNumber();
        $this->parseConfirmationCode();

        if (!$this->amount || !$this->wallet || $this->code) {
            throw new \Exception('Response from service is incorrect');
        }

        return [
            'amount' => $this->amount,
            'wallet' => $this->wallet,
            'code' => $this->code
        ];
    }

    /**
     * Parse confirmation code [digit 4-6]
     */
    private function parseConfirmationCode()
    {
        preg_match('/(?P<code>\d{4,6})(<br>)?/m', $this->response, $matches);

        $this->code = $matches['code'] ?? null;
    }

    /**
     * Parse amount value [digit with . or ,]
     */
    private function parseAmount()
    {
        preg_match('/(?P<amount>\d+[.,]?\d*)\s*р/', $this->response, $matches);

        $this->amount = $matches['amount'] ?? null;
    }

    /**
     * Parse wallet number [digit 11-20]
     */
    private function parseWalletNumber()
    {
        preg_match('/(?P<wallet>\d{11,20})/m', $this->response, $walletMatches);

        $this->wallet = $walletMatches['wallet'] ?? null;
    }
}

$parser = new YandexMoneyMessageParser();

$responseOptions = [
    'Пароль: 3569<br> Спишется 2,02р.<br> Перевод на счет 4100175017397',
    'Кошелек Яндекс.Денег указан неверно.',
    'Недостаточно средств.',
    'Пароль: 4399 Спишется 34,18р. Перевод на счет 4100175017397',
    'Пароль: 8985 Спишется 1010,07рgerger. Перевод на счет 4100175017397',
    'Никому не говорите пароль! Его спрашивают только мошенники.<br> ."\n ".Пароль: 58814<br> ."\n ". Перевод на счет 4100175017392<br> ."\n ". Вы потратите 5371,86р.',
    'Никому не говорите пароль! Его спрашивают только мошенники.<br> Пароль: 79140<br> Перевод на счет 4100175017392<br> Вы потратите 10000р.'
];

foreach ($responseOptions as $response) {
    print_r($parser->setResponse($response)->parse());
}
