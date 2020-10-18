<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\Download;

use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\Download\DownloadMatcher;

class DownloadMatcherTest extends TestCase
{
    public function testMatchesWithACFUrlReturnsTrue()
    {
        $url = "https://connect.advancedcustomfields.com/v2/plugins/download?p=pro&t=5.8.8";
        $sut = new DownloadMatcher();
        $this->assertTrue($sut->matches($url));
    }

    public function testMatchesWithLegacyACFUrlReturnsTrue()
    {
        $url = "https://connect.advancedcustomfields.com/index.php?p=pro&a=download&t=5.8.7";
        $sut = new DownloadMatcher();
        $this->assertTrue($sut->matches($url));
    }

    public function testMatchesWithOtherUrlReturnsFalse()
    {
        $url = "https://example.com/download?key=advancedcustomfields";
        $sut = new DownloadMatcher();
        $this->assertFalse($sut->matches($url));
    }
}
