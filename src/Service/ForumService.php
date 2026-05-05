<?php

namespace App\Service;

use App\Entity\Thread;

class ForumService
{
    /**
     * Rule: A thread is popular if it has more than 5 likes or more than 3 responses.
     */
    public function isPopular(Thread $thread): bool
    {
        return $thread->getLikeCount() > 5 || $thread->getResponses()->count() > 3;
    }

    /**
     * Rule: Identify threads containing moderated content.
     */
    public function isModerated(Thread $thread): bool
    {
        $content = mb_strtolower($thread->getContent() ?? '');
        $forbidden = ['spam', 'arnaque', 'casino', 'publicité', 'gratuit'];

        foreach ($forbidden as $word) {
            if (str_contains($content, $word)) {
                return true;
            }
        }

        return false;
    }
}
