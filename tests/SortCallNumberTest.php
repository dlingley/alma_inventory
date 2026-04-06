<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../SortCallNumber.php';

class SortCallNumberTest extends TestCase
{
    // =========================================================================
    // Dewey Decimal Sorting Tests
    // =========================================================================

    /**
     * Reported bug: 866.09 N8576 was incorrectly sorted before 866.09 N857 2000 v.1
     * because the cutter digits were not padded, causing '_' (ASCII 95) to beat '6' (ASCII 54).
     * Correct order: N857 (shorter cutter = smaller decimal) comes before N8576.
     */
    public function testDeweyReportedBug_CutterDecimalOrder(): void
    {
        $items = [
            ['call_number' => '866.09 N8576'],
            ['call_number' => '866.09 N857 2000 v.1'],
        ];
        usort($items, 'SortDeweyObject');

        $this->assertEquals('866.09 N857 2000 v.1', $items[0]['call_number']);
        $this->assertEquals('866.09 N8576',         $items[1]['call_number']);
    }

    /** Basic Dewey: numeric class ordering */
    public function testDeweyBasicNumericOrder(): void
    {
        $items = [
            ['call_number' => '823 H325'],
            ['call_number' => '100 A123'],
            ['call_number' => '500 B456'],
        ];
        usort($items, 'SortDeweyObject');

        $this->assertEquals('100 A123', $items[0]['call_number']);
        $this->assertEquals('500 B456', $items[1]['call_number']);
        $this->assertEquals('823 H325', $items[2]['call_number']);
    }

    /** Decimal portion ordering: 759.06 before 759.1 */
    public function testDeweyDecimalPortionOrder(): void
    {
        $items = [
            ['call_number' => '759.1 H766'],
            ['call_number' => '759.06 E96'],
        ];
        usort($items, 'SortDeweyObject');

        $this->assertEquals('759.06 E96',  $items[0]['call_number']);
        $this->assertEquals('759.1 H766', $items[1]['call_number']);
    }

    /** Cutter letter ordering: A before B */
    public function testDeweyCutterLetterOrder(): void
    {
        $items = [
            ['call_number' => '823 H325'],
            ['call_number' => '823 A100'],
            ['call_number' => '823 Z999'],
        ];
        usort($items, 'SortDeweyObject');

        $this->assertEquals('823 A100', $items[0]['call_number']);
        $this->assertEquals('823 H325', $items[1]['call_number']);
        $this->assertEquals('823 Z999', $items[2]['call_number']);
    }

    /** Cutter decimal: shorter cutter sorts before extended cutter at same prefix */
    public function testDeweyCutterDecimalPadding(): void
    {
        $items = [
            ['call_number' => '823 H3256'],
            ['call_number' => '823 H325'],
            ['call_number' => '823 H3259'],
        ];
        usort($items, 'SortDeweyObject');

        $this->assertEquals('823 H325',  $items[0]['call_number']);
        $this->assertEquals('823 H3256', $items[1]['call_number']);
        $this->assertEquals('823 H3259', $items[2]['call_number']);
    }

    /** Year/edition as part of call number: earlier year sorts first */
    public function testDeweyYearOrder(): void
    {
        $items = [
            ['call_number' => '823 H325 2005'],
            ['call_number' => '823 H325 1998'],
            ['call_number' => '823 H325 2010'],
        ];
        usort($items, 'SortDeweyObject');

        $this->assertEquals('823 H325 1998', $items[0]['call_number']);
        $this->assertEquals('823 H325 2005', $items[1]['call_number']);
        $this->assertEquals('823 H325 2010', $items[2]['call_number']);
    }

    /** Volume ordering: v.1 before v.2 */
    public function testDeweyVolumeOrder(): void
    {
        $items = [
            ['call_number' => '866.09 N857 2000 v.2'],
            ['call_number' => '866.09 N857 2000 v.1'],
        ];
        usort($items, 'SortDeweyObject');

        $this->assertEquals('866.09 N857 2000 v.1', $items[0]['call_number']);
        $this->assertEquals('866.09 N857 2000 v.2', $items[1]['call_number']);
    }

    /** Mixed Dewey with no decimal */
    public function testDeweyNoDecimal(): void
    {
        $items = [
            ['call_number' => '823 H325'],
            ['call_number' => '822 S527'],
        ];
        usort($items, 'SortDeweyObject');

        $this->assertEquals('822 S527', $items[0]['call_number']);
        $this->assertEquals('823 H325', $items[1]['call_number']);
    }

    /** Known problem call numbers from the source file comments (excluding attached-cutter case) */
    public function testDeweyKnownProblemCallNumbers(): void
    {
        $items = [
            ['call_number' => '759.06 E96'],
            ['call_number' => '704.94978 S727'],
            ['call_number' => '709.04 M453'],
        ];
        usort($items, 'SortDeweyObject');

        $this->assertEquals('704.94978 S727', $items[0]['call_number']);
        $this->assertEquals('709.04 M453',    $items[1]['call_number']);
        $this->assertEquals('759.06 E96',     $items[2]['call_number']);
    }

    /**
     * Known limitation: when the decimal and cutter are run together without
     * a space (e.g. "759.1N"), the decimal digit and cutter letter land in the
     * same token after splitting on ".".  The normalizer cannot distinguish
     * "1" (decimal) from "N" (cutter), so this form does not sort correctly
     * relative to call numbers that do use a space (e.g. "759.06 E96").
     * This test documents the current behavior so regressions are detectable.
     */
    public function testDeweyAttachedCutterKnownLimitation(): void
    {
        $normalized = normalizeDewey('759.1N');
        // token "1n" is not recognised as a second digit group, so the decimal
        // portion is missing its padding and the key comes out as if the class
        // were "759" with no decimal.
        $this->assertEquals('759_000000000000000_1n', $normalized);
    }

    /** SortDewey (non-object) returns negative when left < right */
    public function testSortDeweyComparatorSign(): void
    {
        $this->assertLessThan(0, SortDewey('100 A100', '200 B200'));
        $this->assertGreaterThan(0, SortDewey('200 B200', '100 A100'));
        $this->assertEquals(0, SortDewey('100 A100', '100 A100'));
    }

    // =========================================================================
    // LC (Library of Congress) Sorting Tests
    // =========================================================================

    /** Basic LC: initial letters order (PR before PS) */
    public function testLCInitialLettersOrder(): void
    {
        $items = [
            ['call_number' => 'PS3545 .H16 F6'],
            ['call_number' => 'PR6039 .O32 F5'],
        ];
        usort($items, 'SortLCObject');

        $this->assertEquals('PR6039 .O32 F5',  $items[0]['call_number']);
        $this->assertEquals('PS3545 .H16 F6', $items[1]['call_number']);
    }

    /** LC class number ordering: lower number first */
    public function testLCClassNumberOrder(): void
    {
        $items = [
            ['call_number' => 'PS3545 .H16'],
            ['call_number' => 'PS111 .A11'],
            ['call_number' => 'PS9999 .Z99'],
        ];
        usort($items, 'SortLCObject');

        $this->assertEquals('PS111 .A11',  $items[0]['call_number']);
        $this->assertEquals('PS3545 .H16', $items[1]['call_number']);
        $this->assertEquals('PS9999 .Z99', $items[2]['call_number']);
    }

    /** LC cutter ordering: A before H before Z */
    public function testLCCutterLetterOrder(): void
    {
        $items = [
            ['call_number' => 'PS3545 .Z99'],
            ['call_number' => 'PS3545 .A11'],
            ['call_number' => 'PS3545 .H16'],
        ];
        usort($items, 'SortLCObject');

        $this->assertEquals('PS3545 .A11', $items[0]['call_number']);
        $this->assertEquals('PS3545 .H16', $items[1]['call_number']);
        $this->assertEquals('PS3545 .Z99', $items[2]['call_number']);
    }

    /** LC volume ordering via v. marker */
    public function testLCVolumeOrder(): void
    {
        $items = [
            ['call_number' => 'QA76.9 .D3 S54 v.3'],
            ['call_number' => 'QA76.9 .D3 S54 v.1'],
            ['call_number' => 'QA76.9 .D3 S54 v.2'],
        ];
        usort($items, 'SortLCObject');

        $this->assertEquals('QA76.9 .D3 S54 v.1', $items[0]['call_number']);
        $this->assertEquals('QA76.9 .D3 S54 v.2', $items[1]['call_number']);
        $this->assertEquals('QA76.9 .D3 S54 v.3', $items[2]['call_number']);
    }

    /** LC two-cutter ordering */
    public function testLCTwoCutterOrder(): void
    {
        $items = [
            ['call_number' => 'PN1650 .C6 H45'],
            ['call_number' => 'PN1650 .C6 A22'],
        ];
        usort($items, 'SortLCObject');

        $this->assertEquals('PN1650 .C6 A22', $items[0]['call_number']);
        $this->assertEquals('PN1650 .C6 H45', $items[1]['call_number']);
    }

    /** SortLC (non-object) returns correct sign */
    public function testSortLCComparatorSign(): void
    {
        $this->assertLessThan(0, SortLC('PR100 .A1', 'PS200 .B2'));
        $this->assertGreaterThan(0, SortLC('PS200 .B2', 'PR100 .A1'));
        $this->assertEquals(0, SortLC('PR100 .A1', 'PR100 .A1'));
    }
}
