<?php

namespace MeetupLottery;

use DMS\Service\Meetup\Response\MultiResultResponse;
use DMS\Service\Meetup\MeetupKeyAuthClient;
use Symfony\Component\HttpFoundation\Session\Session;

class Draw
{
    /**
     * @var MultiResultResponse
     */
    protected $response;

    /**
     * @var MeetupKeyAuthClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var int
     */
    protected $eventId;

    /**
     * @var array
     */
    protected $members;

    /**
     * @var array
     */
    protected $drawnMembers = [];

    /**
     * @param array     $config
     * @param Session   $session
     */
    public function __construct($config, Session $session)
    {
        $this->config = $config;
        $this->session = $session;

        if ($session->has('drawn_members')) {
            $this->drawnMembers = $session->get('drawn_members');
        }
    }

    /**
     * @param $eventId
     * @return $this
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * @return MeetupKeyAuthClient
     */
    protected function createClient()
    {
        return MeetupKeyAuthClient::factory(array('key' => $this->config['api_key']));
    }

    /**
     * @return MeetupKeyAuthClient
     */
    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = $this->createClient();
        }

        return $this->client;
    }

    /**
     * @param MultiResultResponse $response
     * @return array
     */
    protected function getMembersFromResponse(MultiResultResponse $response)
    {
        $members = [];

        foreach ($response as $responseItem) {
            if ($responseItem['response'] === 'yes') {
                $member = $responseItem['member'];
                if (isset($responseItem['member_photo'])) {
                    $member['photo'] = $responseItem['member_photo']['thumb_link'];
                }
                $members[] = $member;
            }
        }

        return $members;
    }

    /**
     * @return MultiResultResponse
     */
    protected function getResponse()
    {
        if ($this->response === null) {
            $this->response = $this->getClient()->getRSVPs(['event_id' => $this->eventId]);
        }

        return $this->response;
    }

    /**
     * @return array
     */
    public function getMembers()
    {
        if ($this->eventId === null) {
            return [];
        }

        if ($this->members === null) {
            $this->members = $this->getMembersFromResponse($this->getResponse());
        }

        return $this->members;
    }

    /**
     * @param array $member
     * @return bool
     */
    protected function isDrawnMember($member)
    {
        foreach ($this->drawnMembers as $drawnMember) {
            if ($drawnMember['member_id'] === $member['member_id']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $members
     * @return array
     */
    protected function findUniqueRandomMember($members)
    {
        $countMembers = count($members);
        for ($i = 0; $i < $countMembers; $i++) {
            $index = array_rand($members);

            if ($this->isDrawnMember($members[$index]) === true) {
                unset($members[$index]);
            } else {
                return $members[$index];
            }
        }
    }

    /**
     *
     */
    public function execute()
    {
        $members = $this->getMembers();

        $randomMember = $this->findUniqueRandomMember($members);

        $this->drawnMembers[] = $randomMember;
        $this->session->set('drawn_members', $this->drawnMembers);
    }

    /**
     * @return array
     */
    public function getDrawnMembers()
    {
        return $this->drawnMembers;
    }

    /**
     * @return int
     */
    public function getCountRsvpedMembers()
    {
        return count($this->getMembers());
    }

    public function exportToCsv()
    {
        $handle = fopen('php://memory', 'w');

        foreach ($this->getDrawnMembers() as $drawnMember) {
            fputcsv($handle, $drawnMember);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="drawn_members_' . date('Y-m-d-H-i-s') . '.csv"');

        rewind($handle);
        fpassthru($handle);

        exit;
    }
} 