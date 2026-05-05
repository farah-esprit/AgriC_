<?php

namespace App\Tests\Service;

use App\Entity\Thread;
use App\Entity\Response;
use App\Service\ForumService;
use PHPUnit\Framework\TestCase;

class ForumServiceTest extends TestCase
{
    private ForumService $forumService;

    protected function setUp(): void
    {
        $this->forumService = new ForumService();
    }

    public function testIsPopular(): void
    {
        $thread = new Thread();

        // Not popular
        $this->assertFalse($this->forumService->isPopular($thread));

        // Popular by likes
        $thread->setLikeCount(6);
        $this->assertTrue($this->forumService->isPopular($thread));

        // Popular by responses
        $thread->setLikeCount(0);
        for ($i = 0; $i < 4; $i++) {
            $thread->addResponse(new Response());
        }
        $this->assertTrue($this->forumService->isPopular($thread));
    }

    public function testIsModerated(): void
    {
        $thread = new Thread();

        // Clean content
        $thread->setContent('Comment planter des tomates ?');
        $this->assertFalse($this->forumService->isModerated($thread));

        // Moderated content
        $thread->setContent('Gagnez de l\'argent avec ce spam gratuit !');
        $this->assertTrue($this->forumService->isModerated($thread));
    }
}
