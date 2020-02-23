<?php
declare(strict_types=1);

namespace LicenseKeyProviders\DotEnv;

use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv\DotEnvAdapterFactory;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv\DotEnvV3Adapter;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv\DotEnvV4Adapter;

class DotEnvAdapterFactoryTest extends TestCase
{
    public function testBuildWithV4ReturnsV4Adapter()
    {
        $mock = new class {
            public function createImmutable()
            {
                return;
            }
        };
        $this->assertInstanceOf(DotEnvV4Adapter::class, DotEnvAdapterFactory::build($mock));
    }

    public function testBuildWithV34ReturnsV3Adapter()
    {
        $mock = new class {
            public function create()
            {
                return;
            }
        };
        $this->assertInstanceOf(DotEnvV3Adapter::class, DotEnvAdapterFactory::build($mock));
    }

    public function testBuildReturnsCorrectAdapter()
    {
        if (DotEnvAdapterFactory::isV4()) {
            $mock = new class {
                public function createImmutable()
                {
                    return;
                }
            };
        } else {
            $mock = new class {
                public function create()
                {
                    return;
                }
            };
        }
        $this->assertInstanceOf(get_class(DotEnvAdapterFactory::build()), DotEnvAdapterFactory::build($mock));
    }
}