<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderArticleTestTrait;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderCkEditorTestTrait;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderParagraphsTestTrait;

/**
 * Tests the paragraph split module integration.
 *
 * @group Thunder
 */
class ParagraphSplitTest extends ThunderJavascriptTestBase {

  use ThunderParagraphsTestTrait;
  use ThunderArticleTestTrait;
  use ThunderCkEditorTestTrait;

  /**
   * Field name for paragraphs in article content.
   *
   * @var string
   */
  protected static $paragraphsField = 'field_paragraphs';

  /**
   * Selector template for CKEditor instances.
   *
   * To use it, you have to provide a string containing the paragraphs field
   * name and the delta of the paragraph.
   *
   * @var string
   */
  protected static $selectorTemplate = "textarea[name='%s[%d][subform][field_text][0][value]']";

  /**
   * Test split of paragraph before a selection.
   */
  public function testParagraphSplitBefore(): void {
    $firstParagraphContent = '<p>Content that will be in the first paragraph after the split.</p>';
    $secondParagraphContent = '<p>Content that will be in the second paragraph after the split.</p>';

    $this->nodeFillNew([], 'article');

    // Add text paragraph with two elements.
    $this->addTextParagraph(static::$paragraphsField, $firstParagraphContent . $secondParagraphContent);

    // Select second element in editor.
    $this->selectCkEditorElement($this->getCkEditorCssSelector(0), 1);

    // Split text paragraph before the current selection.
    $this->clickParagraphSplitButton();
    $this->assertWaitOnAjaxRequest();

    // Test if all texts are in the correct paragraph.
    $this->assertCkEditorContent($this->getCkEditorCssSelector(0), $firstParagraphContent . PHP_EOL);
    $this->assertCkEditorContent($this->getCkEditorCssSelector(1), $secondParagraphContent . PHP_EOL);
  }

  /**
   * Test if a deleted paragraph leads to data loss.
   */
  public function testParagraphSplitDataLoss(): void {
    $firstParagraphContent = '<p>Content that will be in the first paragraph after the split.</p>';
    $secondParagraphContent = '<p>Content that will be in the second paragraph after the split.</p>';

    $this->nodeFillNew([], 'article');

    // Create first paragraph.
    $this->addTextParagraph(static::$paragraphsField, '');

    // Remove the paragraph.
    $driver = $this->getSession()->getDriver();
    $driver->executeScript("jQuery('[name=\"field_paragraphs_0_remove\"]').trigger('mousedown')");
    $this->assertWaitOnAjaxRequest();

    // Create second paragraph.
    $this->addTextParagraph(static::$paragraphsField, $firstParagraphContent . $secondParagraphContent);

    // Select second element in editor.
    $this->selectCkEditorElement($this->getCkEditorCssSelector(1), 1);

    // Split text paragraph.
    $this->clickParagraphSplitButton();
    $this->assertWaitOnAjaxRequest();

    // Test if all texts are in the correct paragraph.
    $this->assertCkEditorContent($this->getCkEditorCssSelector(1), $firstParagraphContent . PHP_EOL);
    $this->assertCkEditorContent($this->getCkEditorCssSelector(2), $secondParagraphContent . PHP_EOL);
  }

  /**
   * Test if a adding paragraph after split leads to data loss.
   */
  public function testAddParagraphAfterSplitDataLoss(): void {
    $firstParagraphContent = '<p>Content that will be in the first paragraph after the split.</p>';
    $secondParagraphContent = '<p>Content that will be in the second paragraph after the split.</p>';
    $thirdParagraphContent = '<p>Content that will be placed into the first paragraph after split.</p>';

    $this->nodeFillNew([], 'article');

    // Create first paragraph.
    $this->addTextParagraph(static::$paragraphsField, $firstParagraphContent . $secondParagraphContent);

    // Select second element in editor.
    $this->selectCkEditorElement($this->getCkEditorCssSelector(0), 1);

    // Split text paragraph.
    $this->clickParagraphSplitButton();
    $this->assertWaitOnAjaxRequest();

    $paragraphDelta = $this->getParagraphDelta(static::$paragraphsField, 0);
    $ckEditorCssSelector = "textarea[name='" . static::$paragraphsField . "[{$paragraphDelta}][subform][field_text][0][value]']";

    $this->fillCkEditor(
      $ckEditorCssSelector,
      $thirdParagraphContent
    );

    $ckEditorId = $this->getCkEditorId($ckEditorCssSelector);
    $this->getSession()
      ->getDriver()
      ->executeScript("window.ed = CKEDITOR.instances[\"$ckEditorId\"]; window.ed.setData(\"$thirdParagraphContent\"); window.ed.updateElement(); window.ed.element.data('editor-value-is-changed', true);");

    $this->addTextParagraph(static::$paragraphsField, '', 'text', 1);

    // Test if all texts are in the correct paragraph.
    $this->assertCkEditorContent($this->getCkEditorCssSelector(0), $thirdParagraphContent . PHP_EOL);
    $this->assertCkEditorContent($this->getCkEditorCssSelector(2), '');
    $this->assertCkEditorContent($this->getCkEditorCssSelector(1), $secondParagraphContent . PHP_EOL);
  }

  /**
   * Click on split button.
   */
  protected function clickParagraphSplitButton(): void {
    $this->getSession()->executeScript("jQuery('.cke_button__splittext')[0].click();");
  }

  /**
   * Create css selector for paragraph with the given delta.
   *
   * @param int $paragraphDelta
   *   The delta of the paragraph.
   *
   * @return string
   *   Css selector for the paragraph.
   */
  protected function getCkEditorCssSelector(int $paragraphDelta): string {
    return sprintf(static::$selectorTemplate, static::$paragraphsField, $paragraphDelta);
  }

}
