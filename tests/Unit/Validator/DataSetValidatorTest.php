<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use webignition\BasilLoader\Validator\DataSetValidator;
use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\InvalidResultInterface;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilLoader\Validator\ValidResult;
use webignition\BasilModels\Model\DataParameter\DataParameter;
use webignition\BasilModels\Model\DataParameter\DataParameterInterface;
use webignition\BasilModels\Model\DataSet\DataSet;
use webignition\BasilModels\Model\DataSet\DataSetInterface;

class DataSetValidatorTest extends TestCase
{
    private DataSetValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = DataSetValidator::create();
    }

    public function testValidateIsValid(): void
    {
        $dataSet = new DataSet('0', [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value1',
        ]);

        $expectedResult = new ValidResult($dataSet);

        $this->assertEquals($expectedResult, $this->validator->validate($dataSet, new DataParameter('$data.key1')));
        $this->assertEquals($expectedResult, $this->validator->validate($dataSet, new DataParameter('$data.key2')));
        $this->assertEquals($expectedResult, $this->validator->validate($dataSet, new DataParameter('$data.key3')));
    }

    /**
     * @dataProvider invalidDataSetDataProvider
     */
    public function testValidateNotValid(
        DataSetInterface $dataSet,
        DataParameterInterface $dataParameter,
        InvalidResultInterface $expectedResult
    ): void {
        $this->assertEquals($expectedResult, $this->validator->validate($dataSet, $dataParameter));
    }

    /**
     * @return array<mixed>
     */
    public static function invalidDataSetDataProvider(): array
    {
        return [
            'empty' => [
                'dataSet' => new DataSet('0', []),
                'dataParameter' => new DataParameter('$data.key'),
                'expectedResult' => (new InvalidResult(
                    new DataSet('0', []),
                    ResultType::DATASET,
                    DataSetValidator::REASON_DATASET_INCOMPLETE
                ))->withContext([
                    DataSetValidator::CONTEXT_DATA_PARAMETER_NAME => 'key',
                ]),
            ],
            'key not present' => [
                'dataSet' => new DataSet('0', [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ]),
                'dataParameter' => new DataParameter('$data.key3'),
                'expectedResult' => (new InvalidResult(
                    new DataSet('0', [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ]),
                    ResultType::DATASET,
                    DataSetValidator::REASON_DATASET_INCOMPLETE
                ))->withContext([
                    DataSetValidator::CONTEXT_DATA_PARAMETER_NAME => 'key3',
                ]),
            ],
        ];
    }
}
