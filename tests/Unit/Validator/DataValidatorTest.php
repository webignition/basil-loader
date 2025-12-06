<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use webignition\BasilLoader\Validator\DataSetValidator;
use webignition\BasilLoader\Validator\DataValidator;
use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\InvalidResultInterface;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilLoader\Validator\ValidResult;
use webignition\BasilModels\Model\DataParameter\DataParameter;
use webignition\BasilModels\Model\DataParameter\DataParameterInterface;
use webignition\BasilModels\Model\DataSet\DataSet;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\DataSet\DataSetCollectionInterface;

class DataValidatorTest extends TestCase
{
    private DataValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = DataValidator::create();
    }

    public function testValidateIsValid(): void
    {
        $data = new DataSetCollection([
            '0' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            '1' => [
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3',
            ],
        ]);

        $expectedResult = new ValidResult($data);

        $this->assertEquals($expectedResult, $this->validator->validate($data, new DataParameter('$data.key1')));
        $this->assertEquals($expectedResult, $this->validator->validate($data, new DataParameter('$data.key2')));
    }

    /**
     * @dataProvider invalidDataSetDataProvider
     */
    public function testValidateNotValid(
        DataSetCollectionInterface $data,
        DataParameterInterface $dataParameter,
        InvalidResultInterface $expectedResult
    ): void {
        $this->assertEquals($expectedResult, $this->validator->validate($data, $dataParameter));
    }

    /**
     * @return array<mixed>
     */
    public function invalidDataSetDataProvider(): array
    {
        return [
            'empty' => [
                'data' => new DataSetCollection([]),
                'dataParameter' => new DataParameter('$data.key'),
                'expectedResult' => new InvalidResult(
                    new DataSetCollection([]),
                    ResultType::DATA,
                    DataValidator::REASON_DATA_EMPTY
                ),
            ],
            'key not present' => [
                'data' => new DataSetCollection([
                    '0' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                    '1' => [
                        'key2' => 'value2',
                    ],
                ]),
                'dataParameter' => new DataParameter('$data.key1'),
                'expectedResult' => new InvalidResult(
                    new DataSetCollection([
                        '0' => [
                            'key1' => 'value1',
                            'key2' => 'value2',
                        ],
                        '1' => [
                            'key2' => 'value2',
                        ],
                    ]),
                    ResultType::DATA,
                    DataValidator::REASON_DATASET_INVALID,
                    (new InvalidResult(
                        new DataSet('1', ['key2' => 'value2']),
                        ResultType::DATASET,
                        DataSetValidator::REASON_DATASET_INCOMPLETE
                    ))->withContext([
                        DataSetValidator::CONTEXT_DATA_PARAMETER_NAME => 'key1',
                    ])
                ),
            ],
        ];
    }
}
