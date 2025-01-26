<?php

namespace App\Tests\Service;

use App\Repository\ProviderRepository;
use App\Service\ProviderMatchingService;
use PHPUnit\Framework\TestCase;

class ProviderMatchingServiceTest extends TestCase
{
    private ProviderRepository $repository;
    private ProviderMatchingService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProviderRepository::class);
        $this->service = new ProviderMatchingService($this->repository);
    }

    public function testMatchTopicsWithMatchingTopics(): void
    {
        $teacherTopics = [
            'math' => true,
            'science' => true,
        ];

        $providerData = [
            'provider_a' => 'math+history',
            'provider_b' => 'science+english',
        ];

        $this->repository
            ->method('getProviders')
            ->willReturn($providerData);

        $expected = [
            'provider_a' => ['math'],
            'provider_b' => ['science'],
        ];

        $result = $this->service->matchTopics($teacherTopics);

        $this->assertEquals($expected, $result);
    }

    public function testMatchTopicsWithNoMatchingTopics(): void
    {
        $teacherTopics = [
            'math' => true,
            'science' => true,
        ];

        $providerData = [
            'provider_a' => 'history+reading',
            'provider_b' => 'history+art',
        ];

        $this->repository
            ->method('getProviders')
            ->willReturn($providerData);

        $result = $this->service->matchTopics($teacherTopics);

        $this->assertEmpty($result);
    }

    public function testMatchTopicsWithEmptyTeacherTopics(): void
    {
        $teacherTopics = [];

        $providerData = [
            'provider_a' => 'math+science',
        ];

        $this->repository
            ->method('getProviders')
            ->willReturn($providerData);

        $result = $this->service->matchTopics($teacherTopics);

        $this->assertEmpty($result);
    }

    public function testMatchTopicsWithEmptyProviderData(): void
    {
        $teacherTopics = [
            'math' => true,
            'science' => true,
        ];

        $this->repository
            ->method('getProviders')
            ->willReturn([]);

        $result = $this->service->matchTopics($teacherTopics);

        $this->assertEmpty($result);
    }

    public function testMatchTopicsWithPartialMatches(): void
    {
        $teacherTopics = [
            'math' => true,
            'science' => true,
            'art' => true,
        ];

        $providerData = [
            'ProviderA' => 'math+english+art',
            'ProviderB' => 'science+history',
        ];

        $expected = [
            'ProviderA' => ['math', 'art'],
            'ProviderB' => ['science'],
        ];

        $this->repository
            ->method('getProviders')
            ->willReturn($providerData);

        $result = $this->service->matchTopics($teacherTopics);

        $this->assertEquals($expected, $result);
    }
}