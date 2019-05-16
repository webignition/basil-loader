<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Factory\Action;

use webignition\BasilParser\Factory\Action\ActionFactory;
use webignition\BasilParser\Factory\Action\InteractionActionFactory;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;

class ActionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $interactionActionParser = new InteractionActionFactory();

        $this->actionFactory = new ActionFactory([
            $interactionActionParser,
        ]);
    }

    /**
     * @dataProvider createFromClickActionStringForValidInteractionActionDataProvider
     * @dataProvider createFromSubmitActionStringForValidInteractionActionDataProvider
     * @dataProvider createFromWaitForActionStringForValidInteractionActionDataProvider
     */
    public function testCreateFromActionStringForValidInteractionAction(
        string $action,
        string $expectedVerb,
        IdentifierInterface $expectedIdentifier
    ) {
        $action = $this->actionFactory->createFromActionString($action);

        $this->assertInstanceOf(InteractionAction::class, $action);
        $this->assertSame($expectedVerb, $action->getVerb());

        if ($action instanceof InteractionAction) {
            $this->assertEquals($expectedIdentifier, $action->getIdentifier());
        }
    }

    public function createFromClickActionStringForValidInteractionActionDataProvider(): array
    {
        return [
            'click css selector with null position double-quoted' => [
                'action' => 'click ".sign-in-form .submit-button"',
                'expectedVerb' => ActionTypes::CLICK,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button'
                ),
            ],
            'click css selector with position double-quoted' => [
                'action' => 'click ".sign-in-form .submit-button":3',
                'expectedVerb' => ActionTypes::CLICK,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button',
                    3
                ),
            ],
            'click css selector unquoted is treated as page model element reference' => [
                'action' => 'click .sign-in-form .submit-button',
                'expectedVerb' => ActionTypes::CLICK,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    '.sign-in-form .submit-button'
                ),
            ],
            'click page model reference' => [
                'action' => 'click imported_page_model.elements.element_name',
                'expectedVerb' => ActionTypes::CLICK,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    'imported_page_model.elements.element_name'
                ),
            ],
            'click element parameter reference' => [
                'action' => 'click $elements.name',
                'expectedVerb' => ActionTypes::CLICK,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::ELEMENT_PARAMETER,
                    '$elements.name'
                ),
            ],
//            'submit css selector double-quoted' => [
//                'action' => 'submit ".sign-in-form .submit-button"',
//                'expectedVerb' => ActionTypes::SUBMIT,
//                'expectedIdentifier' => '.sign-in-form .submit-button',
//            ],
//            'submit css selector unquoted' => [
//                'action' => 'submit .sign-in-form .submit-button',
//                'expectedVerb' => ActionTypes::SUBMIT,
//                'expectedIdentifier' => '.sign-in-form .submit-button',
//            ],
//            'submit page model reference' => [
//                'action' => 'submit imported_page_model.elements.element_name',
//                'expectedVerb' => ActionTypes::SUBMIT,
//                'expectedIdentifier' => 'imported_page_model.elements.element_name',
//            ],
//            'submit element parameter reference' => [
//                'action' => 'submit $elements.name',
//                'expectedVerb' => ActionTypes::SUBMIT,
//                'expectedIdentifier' => '$elements.name',
//            ],
//            'wait-for css selector double-quoted' => [
//                'action' => 'wait-for ".sign-in-form .submit-button"',
//                'expectedVerb' => ActionTypes::WAIT_FOR,
//                'expectedIdentifier' => '.sign-in-form .submit-button',
//            ],
//            'wait-for css selector unquoted' => [
//                'action' => 'wait-for .sign-in-form .submit-button',
//                'expectedVerb' => ActionTypes::WAIT_FOR,
//                'expectedIdentifier' => '.sign-in-form .submit-button',
//            ],
//            'wait-for page model reference' => [
//                'action' => 'wait-for imported_page_model.elements.element_name',
//                'expectedVerb' => ActionTypes::WAIT_FOR,
//                'expectedIdentifier' => 'imported_page_model.elements.element_name',
//            ],
//            'wait-for element parameter reference' => [
//                'action' => 'wait-for $elements.name',
//                'expectedVerb' => ActionTypes::WAIT_FOR,
//                'expectedIdentifier' => '$elements.name',
//            ],
        ];
    }

    public function createFromSubmitActionStringForValidInteractionActionDataProvider(): array
    {
        return [
            'submit css selector with null position double-quoted' => [
                'action' => 'submit ".sign-in-form .submit-button"',
                'expectedVerb' => ActionTypes::SUBMIT,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button'
                ),
            ],
            'submit css selector with position double-quoted' => [
                'action' => 'submit ".sign-in-form .submit-button":3',
                'expectedVerb' => ActionTypes::SUBMIT,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button',
                    3
                ),
            ],
            'submit css selector unquoted is treated as page model element reference' => [
                'action' => 'submit .sign-in-form .submit-button',
                'expectedVerb' => ActionTypes::SUBMIT,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    '.sign-in-form .submit-button'
                ),
            ],
            'submit page model reference' => [
                'action' => 'submit imported_page_model.elements.element_name',
                'expectedVerb' => ActionTypes::SUBMIT,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    'imported_page_model.elements.element_name'
                ),
            ],
            'submit element parameter reference' => [
                'action' => 'submit $elements.name',
                'expectedVerb' => ActionTypes::SUBMIT,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::ELEMENT_PARAMETER,
                    '$elements.name'
                ),
            ],
        ];
    }

    public function createFromWaitForActionStringForValidInteractionActionDataProvider(): array
    {
        return [
            'wait-for css selector with null position double-quoted' => [
                'action' => 'wait-for ".sign-in-form .submit-button"',
                'expectedVerb' => ActionTypes::WAIT_FOR,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button'
                ),
            ],
            'wait-for css selector with position double-quoted' => [
                'action' => 'wait-for ".sign-in-form .submit-button":3',
                'expectedVerb' => ActionTypes::WAIT_FOR,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button',
                    3
                ),
            ],
            'wait-for css selector unquoted is treated as page model element reference' => [
                'action' => 'wait-for .sign-in-form .submit-button',
                'expectedVerb' => ActionTypes::WAIT_FOR,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    '.sign-in-form .submit-button'
                ),
            ],
            'wait-for page model reference' => [
                'action' => 'wait-for imported_page_model.elements.element_name',
                'expectedVerb' => ActionTypes::WAIT_FOR,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    'imported_page_model.elements.element_name'
                ),
            ],
            'wait-for element parameter reference' => [
                'action' => 'wait-for $elements.name',
                'expectedVerb' => ActionTypes::WAIT_FOR,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::ELEMENT_PARAMETER,
                    '$elements.name'
                ),
            ],
        ];
    }
}
