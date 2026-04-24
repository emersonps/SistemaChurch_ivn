<?php
// src/controllers/HomeController.php

class HomeController {
    public function index() {
        $db = (new Database())->connect();
        
        // Buscar Banners Ativos
        try {
            $banners = $db->query("SELECT * FROM banners WHERE active = 1 ORDER BY display_order ASC, created_at DESC")->fetchAll();
        } catch (PDOException $e) {
            $banners = [];
        }

        $cultos = $db->query("SELECT * FROM events WHERE type = 'culto' AND (status = 'active' OR status IS NULL) ORDER BY event_date ASC")->fetchAll();
        
        // Buscar Convites Especiais (type = 'convite')
        $convites = $db->query("
            SELECT * FROM events 
            WHERE type = 'convite' 
            AND (status = 'active' OR status IS NULL) 
            ORDER BY event_date ASC 
            LIMIT 6
        ")->fetchAll();

        // Buscar Eventos por Congregação (EXCETO culto, convite e interno)
        $eventos = $db->query("
            SELECT * FROM events 
            WHERE type NOT IN ('culto', 'convite', 'interno')
            AND (status = 'active' OR status IS NULL) 
            ORDER BY event_date ASC
        ")->fetchAll();

        // Buscar Congregações (mostrando todas)
        $congregacoes = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        $countdownCultos = $this->buildCountdownCultos($cultos, $congregacoes);
        $homeHighlights = $this->buildHomeHighlights($db, $eventos, $convites);
        
        // Buscar Configurações de Layout do Site
        $site_settings = $db->query("SELECT * FROM site_settings LIMIT 1")->fetch();
        if (!$site_settings) {
            // Default caso a tabela esteja vazia
            $site_settings = [
                'theme_id' => 'theme-1',
                'primary_color' => '#0d6efd',
                'secondary_color' => '#6c757d',
                'font_family' => 'Inter, sans-serif',
                'hero_bg_image' => 'hero_theme_1.jpg'
            ];
        }

        view('public/home', [
            'banners' => $banners,
            'cultos' => $cultos,
            'eventos' => $eventos,
            'convites' => $convites,
            'congregacoes' => $congregacoes,
            'countdownCultos' => $countdownCultos,
            'homeHighlights' => $homeHighlights,
            'site_settings' => $site_settings
        ]);
    }

    private function buildHomeHighlights(PDO $db, array $eventos, array $convites) {
        $members = [];
        try {
            $members = $db->query("SELECT * FROM members ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $members = [];
        }

        $birthdays = $this->extractMonthlyBirthdays($members);
        $newMembers = $this->extractNewMembers($members);
        $baptisms = $this->extractRecentBaptisms($members);
        $latestAlbum = $this->fetchLatestAlbum($db);
        $upcomingItems = $this->buildUpcomingItems($eventos, $convites, $baptisms);

        return [
            'birthdays' => $birthdays,
            'new_members' => $newMembers,
            'baptisms' => $baptisms,
            'latest_album' => $latestAlbum,
            'upcoming_items' => $upcomingItems
        ];
    }

    private function extractMonthlyBirthdays(array $members) {
        $month = date('m');
        $birthdays = array_filter($members, function ($member) use ($month) {
            $birthDate = trim((string)($member['birth_date'] ?? ''));
            return $birthDate !== '' && substr($birthDate, 5, 2) === $month;
        });

        usort($birthdays, function ($left, $right) {
            return strcmp((string)($left['birth_date'] ?? ''), (string)($right['birth_date'] ?? ''));
        });

        $birthdays = array_values($birthdays);
        foreach ($birthdays as &$birthday) {
            $birthday['first_name'] = $this->firstName($birthday['name'] ?? '');
        }
        unset($birthday);

        return $birthdays;
    }

    private function extractNewMembers(array $members) {
        $filtered = array_filter($members, function ($member) {
            if (empty($member['name'])) {
                return false;
            }

            $referenceDate = $member['admission_date'] ?? $member['accepted_jesus_at'] ?? $member['created_at'] ?? '';
            return $this->isCurrentMonthDate($referenceDate);
        });

        usort($filtered, function ($left, $right) {
            $leftDate = $left['admission_date'] ?? $left['accepted_jesus_at'] ?? $left['created_at'] ?? '';
            $rightDate = $right['admission_date'] ?? $right['accepted_jesus_at'] ?? $right['created_at'] ?? '';
            return strcmp((string)$rightDate, (string)$leftDate);
        });

        $filtered = array_values($filtered);
        foreach ($filtered as &$member) {
            $member['first_name'] = $this->firstName($member['name'] ?? '');
        }
        unset($member);

        return $filtered;
    }

    private function extractRecentBaptisms(array $members) {
        $filtered = array_filter($members, function ($member) {
            return !empty($member['baptism_date']) && $this->isCurrentMonthDate($member['baptism_date']);
        });

        usort($filtered, function ($left, $right) {
            return strcmp((string)($right['baptism_date'] ?? ''), (string)($left['baptism_date'] ?? ''));
        });

        $filtered = array_values($filtered);
        foreach ($filtered as &$member) {
            $member['first_name'] = $this->firstName($member['name'] ?? '');
        }
        unset($member);

        return $filtered;
    }

    private function isCurrentMonthDate($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return false;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return false;
        }

        return date('Y-m', $timestamp) === date('Y-m');
    }

    private function fetchLatestAlbum(PDO $db) {
        try {
            $album = $db->query("SELECT * FROM photo_albums ORDER BY event_date DESC, id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if (!$album) {
                return null;
            }

            $stmtPhotos = $db->prepare("SELECT * FROM photos WHERE album_id = ? LIMIT 4");
            $stmtPhotos->execute([$album['id']]);
            $album['photos'] = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

            return $album;
        } catch (Exception $e) {
            return null;
        }
    }

    private function buildUpcomingItems(array $eventos, array $convites, array $baptisms) {
        $now = new DateTimeImmutable('now');
        $items = [];

        foreach ($eventos as $evento) {
            $candidate = $this->buildUpcomingHighlightItem($evento, 'Próximo evento', $now, 'Data a confirmar');
            if ($candidate) {
                $items[] = $candidate;
            }
        }

        foreach ($convites as $convite) {
            $description = trim(strip_tags((string)($convite['description'] ?? '')));
            if ($description !== '') {
                $description = function_exists('mb_strimwidth')
                    ? mb_strimwidth($description, 0, 60, '...')
                    : (strlen($description) > 60 ? substr($description, 0, 57) . '...' : $description);
            }

            $candidate = $this->buildUpcomingHighlightItem(
                $convite,
                'Novo convite',
                $now,
                $description !== '' ? $description : 'Convite especial disponível'
            );
            if ($candidate) {
                $items[] = $candidate;
            }
        }

        usort($items, function ($left, $right) {
            $leftTs = $left['sort_timestamp'] ?? PHP_INT_MAX;
            $rightTs = $right['sort_timestamp'] ?? PHP_INT_MAX;
            return $leftTs <=> $rightTs;
        });

        $items = array_slice($items, 0, 3);

        if (count($items) < 4 && !empty($baptisms)) {
            $items[] = [
                'icon' => null,
                'title' => 'Batismos',
                'subtitle' => count($baptisms) . ' registros recentes de batismo'
            ];
        }

        return array_map(function ($item) {
            unset($item['sort_timestamp']);
            return $item;
        }, array_slice($items, 0, 4));
    }

    private function buildUpcomingHighlightItem(array $event, $fallbackTitle, DateTimeImmutable $now, $fallbackSubtitle) {
        $occurrence = $this->resolveUpcomingEventOccurrence($event, $now);
        if ($occurrence === false) {
            return null;
        }

        return [
            'icon' => !empty($event['banner_path']) ? $event['banner_path'] : null,
            'title' => trim((string)($event['title'] ?? $fallbackTitle)) ?: $fallbackTitle,
            'subtitle' => $occurrence instanceof DateTimeImmutable
                ? $occurrence->format('d/m/Y H:i')
                : $fallbackSubtitle,
            'sort_timestamp' => $occurrence instanceof DateTimeImmutable ? $occurrence->getTimestamp() : PHP_INT_MAX
        ];
    }

    private function resolveUpcomingEventOccurrence(array $event, DateTimeImmutable $now) {
        $eventDate = trim((string)($event['event_date'] ?? ''));
        $endTime = trim((string)($event['end_time'] ?? ''));

        $hadConcreteDate = false;

        if ($eventDate !== '' && strpos($eventDate, '1970-01-01') !== 0) {
            $hadConcreteDate = true;
            $startTimestamp = strtotime($eventDate);
            if ($startTimestamp !== false) {
                $start = (new DateTimeImmutable())->setTimestamp($startTimestamp);
                if ($start >= $now) {
                    return $start;
                }

                if ($endTime !== '') {
                    $end = $this->combineDateAndTime($start, $endTime);
                    if ($end && $end >= $now) {
                        return $start;
                    }
                }
            }

        }

        if (!empty($event['recurring_days'])) {
            $days = json_decode((string)($event['recurring_days'] ?? ''), true);
            if (is_array($days) && !empty($days)) {
                $timeValue = $eventDate !== '' && strtotime($eventDate) !== false ? date('H:i', strtotime($eventDate)) : '19:30';
                return $this->nextDateFromDays($days, $timeValue, $now);
            }
        }

        return $hadConcreteDate ? false : null;
    }

    private function combineDateAndTime(DateTimeImmutable $date, $timeValue) {
        $timeValue = trim((string)$timeValue);
        if ($timeValue === '') {
            return null;
        }

        [$hour, $minute] = array_pad(explode(':', $timeValue), 2, '00');
        return $date->setTime((int)$hour, (int)$minute);
    }

    private function buildCountdownCultos(array $cultos, array $congregacoes) {
        $cards = [];
        $now = new DateTimeImmutable('now');

        foreach ($congregacoes as $congregacao) {
            $congregationId = (int)($congregacao['id'] ?? 0);
            $congregationName = trim((string)($congregacao['name'] ?? 'Congregação'));
            $best = null;

            foreach ($cultos as $culto) {
                $matchesCongregation = false;
                if (!empty($culto['congregation_id']) && (int)$culto['congregation_id'] === $congregationId) {
                    $matchesCongregation = true;
                } elseif (!empty($culto['location']) && trim((string)$culto['location']) === $congregationName) {
                    $matchesCongregation = true;
                }

                if (!$matchesCongregation) {
                    continue;
                }

                $candidate = $this->buildCultoCandidateFromEvent($culto, $congregationName, $now);
                if ($candidate && ($best === null || $candidate['timestamp'] < $best['timestamp'])) {
                    $best = $candidate;
                }
            }

            $scheduleItems = json_decode((string)($congregacao['service_schedule'] ?? ''), true);
            if (is_array($scheduleItems)) {
                foreach ($scheduleItems as $scheduleItem) {
                    $candidate = $this->buildCultoCandidateFromSchedule($scheduleItem, $congregacao, $now);
                    if ($candidate && ($best === null || $candidate['timestamp'] < $best['timestamp'])) {
                        $best = $candidate;
                    }
                }
            }

            if ($best) {
                $cards[] = $best;
            }
        }

        usort($cards, function ($left, $right) {
            return ($left['timestamp'] ?? PHP_INT_MAX) <=> ($right['timestamp'] ?? PHP_INT_MAX);
        });

        return $cards;
    }

    private function buildCultoCandidateFromEvent(array $culto, $congregationName, DateTimeImmutable $now) {
        $eventDate = trim((string)($culto['event_date'] ?? ''));
        $timestamp = null;

        if ($eventDate !== '' && strpos($eventDate, '1970-01-01') !== 0) {
            $parsed = strtotime($eventDate);
            if ($parsed !== false && $parsed > $now->getTimestamp()) {
                $timestamp = $parsed;
            }
        }

        if ($timestamp === null && !empty($culto['recurring_days'])) {
            $days = json_decode((string)$culto['recurring_days'], true);
            if (is_array($days) && !empty($days)) {
                $timeValue = $eventDate !== '' && strtotime($eventDate) !== false ? date('H:i', strtotime($eventDate)) : '19:30';
                $nextDate = $this->nextDateFromDays($days, $timeValue, $now);
                if ($nextDate) {
                    $timestamp = $nextDate->getTimestamp();
                    $eventDate = $nextDate->format('Y-m-d H:i:s');
                }
            }
        }

        if ($timestamp === null) {
            return null;
        }

        $date = (new DateTimeImmutable())->setTimestamp($timestamp);
        return [
            'congregation_name' => $congregationName,
            'title' => trim((string)($culto['title'] ?? 'Culto')),
            'starts_at' => $date->format('Y-m-d H:i:s'),
            'start_label' => $date->format('H:i'),
            'date_label' => $this->formatPortugueseDate($date),
            'weekday_label' => $this->weekdayPortuguese($date),
            'location' => trim((string)($culto['location'] ?? $congregationName)),
            'timestamp' => $timestamp
        ];
    }

    private function buildCultoCandidateFromSchedule(array $scheduleItem, array $congregacao, DateTimeImmutable $now) {
        $day = trim((string)($scheduleItem['day'] ?? ''));
        $startTime = trim((string)($scheduleItem['start_time'] ?? ''));
        if ($day === '' || $startTime === '') {
            return null;
        }

        $nextDate = $this->nextDateFromDays([$day], $startTime, $now);
        if (!$nextDate) {
            return null;
        }

        return [
            'congregation_name' => trim((string)($congregacao['name'] ?? 'Congregação')),
            'title' => trim((string)($scheduleItem['name'] ?? 'Culto congregacional')) ?: 'Culto congregacional',
            'starts_at' => $nextDate->format('Y-m-d H:i:s'),
            'start_label' => $nextDate->format('H:i'),
            'date_label' => $this->formatPortugueseDate($nextDate),
            'weekday_label' => $this->weekdayPortuguese($nextDate),
            'location' => trim((string)($congregacao['address'] ?? '')),
            'timestamp' => $nextDate->getTimestamp()
        ];
    }

    private function nextDateFromDays(array $days, $timeValue, DateTimeImmutable $now) {
        $map = [
            'Domingo' => 0,
            'Segunda' => 1,
            'Terça' => 2,
            'Terca' => 2,
            'Quarta' => 3,
            'Quinta' => 4,
            'Sexta' => 5,
            'Sábado' => 6,
            'Sabado' => 6,
        ];

        $best = null;
        [$hour, $minute] = array_pad(explode(':', $timeValue), 2, '00');

        foreach ($days as $day) {
            $label = trim((string)$day);
            if (!isset($map[$label])) {
                continue;
            }

            $currentWeekday = (int)$now->format('w');
            $targetWeekday = $map[$label];
            $daysAhead = ($targetWeekday - $currentWeekday + 7) % 7;
            $candidate = $now->modify('+' . $daysAhead . ' days')->setTime((int)$hour, (int)$minute);
            if ($candidate <= $now) {
                $candidate = $candidate->modify('+7 days');
            }

            if ($best === null || $candidate < $best) {
                $best = $candidate;
            }
        }

        return $best;
    }

    private function weekdayPortuguese(DateTimeImmutable $date) {
        $map = [
            'Sunday' => 'Domingo',
            'Monday' => 'Segunda-feira',
            'Tuesday' => 'Terça-feira',
            'Wednesday' => 'Quarta-feira',
            'Thursday' => 'Quinta-feira',
            'Friday' => 'Sexta-feira',
            'Saturday' => 'Sábado',
        ];

        return $map[$date->format('l')] ?? $date->format('l');
    }

    private function formatPortugueseDate(DateTimeImmutable $date) {
        return $date->format('d/m/Y');
    }

    private function firstName($name) {
        $name = trim((string)$name);
        if ($name === '') {
            return '';
        }

        $parts = preg_split('/\s+/', $name);
        return $parts[0] ?? $name;
    }
}
