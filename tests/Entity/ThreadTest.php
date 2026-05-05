<?php

namespace App\Tests\Entity;

use App\Entity\Thread;
use App\Entity\User;
use App\Entity\Response;
use PHPUnit\Framework\TestCase;

class ThreadTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $thread = new Thread();
        $title = 'Conseils pour le maïs';
        $content = 'Quels sont vos meilleurs conseils pour la culture du maïs ?';
        $date = new \DateTime();
        $status = 'ACTIVE';

        $thread->setTitle($title)
            ->setContent($content)
            ->setCreatedAt($date)
            ->setStatus($status)
            ->setLikeCount(10);

        $this->assertEquals($title, $thread->getTitle());
        $this->assertEquals($content, $thread->getContent());
        $this->assertEquals($date, $thread->getCreatedAt());
        $this->assertEquals($status, $thread->getStatus());
        $this->assertEquals(10, $thread->getLikeCount());
    }

    public function testAddRemoveResponse(): void
    {
        $thread = new Thread();
        $response = new Response();

        $thread->addResponse($response);
        $this->assertCount(1, $thread->getResponses());
        $this->assertSame($thread, $response->getThread());

        $thread->removeResponse($response);
        $this->assertCount(0, $thread->getResponses());
        $this->assertNull($response->getThread());
    }

    public function testRelationWithUser(): void
    {
        $thread = new Thread();
        $user = new User();
        
        $thread->setUser($user);
        $this->assertSame($user, $thread->getUser());
    }
}
