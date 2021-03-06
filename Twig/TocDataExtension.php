<?php

namespace Soltys\Bundle\SoltysSlateBundle\Twig;

use Parsedown;
use Symfony\Component\DomCrawler\Crawler;

/*
 * This file is part of the SoltysSlateBundle.
 *
 * (c) Hugo Soltys <hugo.soltys@gmail.com>
 *
 * https://hugo-soltys.com
 */
class TocDataExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('toc_data', [$this, 'tocDataFilter']),
        ];
    }

    public function tocDataFilter($pageContent)
    {
        $parser = new Parsedown();
        $md = $parser->text($pageContent);
        $crawler = new Crawler($md);

        $titles = $crawler->filter('h1,h2,h3');

        $headers = $this->crawl($titles);

        $headersToNest = [];
        foreach ($headers as $header) {
            if ($header['level'] == 2) {
                $totalToNest = count($headersToNest);
                if (!in_array($header, $headersToNest[$totalToNest - 1]['children'])) {
                    $headersToNest[$totalToNest - 1]['children'][] = $header;
                }
            } elseif ($header['level'] < 2) {
                if (!in_array($header, $headersToNest)) {
                    $headersToNest[] = $header;
                }
            }
        }

        $headers = $headersToNest;

        return $headers;
    }

    public function crawl(Crawler $nodes)
    {
        $headers = $nodes->each(function (Crawler $node, $loop) {
            $nodeName = $node->nodeName();
            $level = $this->getLevel($nodeName);

            $header['id'] = $node->attr('id');
            $header['content'] = $node->text();
            $header['level'] = $level;
            $header['children'] = [];

            return $header;
        });

        return $headers;
    }

    public function getLevel($nodeName)
    {
        switch ($nodeName) {
            case "h2":
                $level = 2;
                break;
            case "h3":
                $level = 3;
                break;
            default:
                $level = 1;
                break;
        }

        return $level;
    }
}