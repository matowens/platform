<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Provider;

use Oro\Bundle\CalendarBundle\Provider\PublicCalendarEventNormalizer;

class PublicCalendarEventNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $reminderManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityFacade;

    /** @var PublicCalendarEventNormalizer */
    protected $normalizer;

    protected function setUp()
    {
        $this->reminderManager = $this->getMockBuilder('Oro\Bundle\ReminderBundle\Entity\Manager\ReminderManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityFacade  = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();

        $this->normalizer = new PublicCalendarEventNormalizer(
            $this->reminderManager,
            $this->securityFacade
        );
    }

    /**
     * @dataProvider getCalendarEventsProvider
     */
    public function testGetCalendarEvents($events, $expected)
    {
        $calendarId = 123;

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getArrayResult'])
            ->getMockForAbstractClass();
        $query->expects($this->once())
            ->method('getArrayResult')
            ->will($this->returnValue($events));

        $this->reminderManager->expects($this->once())
            ->method('applyReminders')
            ->with($expected, 'Oro\Bundle\CalendarBundle\Entity\CalendarEvent');

        $result = $this->normalizer->getCalendarEvents($calendarId, $query);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getGrantedCalendarEventsProvider
     */
    public function testGetCalendarEventsWithGrantedManagement($events, $expected)
    {
        $calendarId = 123;

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getArrayResult'])
            ->getMockForAbstractClass();
        $query->expects($this->once())
            ->method('getArrayResult')
            ->will($this->returnValue($events));

        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->will($this->returnValue(true));
        $this->reminderManager->expects($this->once())
            ->method('applyReminders')
            ->with($expected, 'Oro\Bundle\CalendarBundle\Entity\CalendarEvent');

        $result = $this->normalizer->getCalendarEvents($calendarId, $query);
        $this->assertEquals($expected, $result);
    }

    public function getCalendarEventsProvider()
    {
        $startDate = new \DateTime();
        $endDate   = $startDate->add(new \DateInterval('PT1H'));

        return [
            [
                'events'   => [],
                'expected' => []
            ],
            [
                'events'   => [
                    [
                        'calendar' => 123,
                        'id'       => 1,
                        'title'    => 'test',
                        'start'    => $startDate,
                        'end'      => $endDate
                    ],
                ],
                'expected' => [
                    [
                        'calendar'  => 123,
                        'id'        => 1,
                        'title'     => 'test',
                        'start'     => $startDate->format('c'),
                        'end'       => $endDate->format('c'),
                        'editable'  => false,
                        'removable' => false
                    ],
                ]
            ],
            [
                'events'   => [
                    [
                        'calendar' => 123,
                        'id'       => 1,
                        'title'    => 'test',
                        'start'    => $startDate,
                        'end'      => $endDate
                    ],
                ],
                'expected' => [
                    [
                        'calendar'  => 123,
                        'id'        => 1,
                        'title'     => 'test',
                        'start'     => $startDate->format('c'),
                        'end'       => $endDate->format('c'),
                        'editable'  => false,
                        'removable' => false
                    ],
                ]
            ],
        ];
    }

    public function getGrantedCalendarEventsProvider()
    {
        $startDate = new \DateTime();
        $endDate   = $startDate->add(new \DateInterval('PT1H'));

        return [
            [
                'events'   => [
                    [
                        'calendar' => 123,
                        'id'       => 1,
                        'title'    => 'test',
                        'start'    => $startDate,
                        'end'      => $endDate
                    ],
                ],
                'expected' => [
                    [
                        'calendar' => 123,
                        'id'       => 1,
                        'title'    => 'test',
                        'start'    => $startDate->format('c'),
                        'end'      => $endDate->format('c'),
                    ],
                ]
            ],
        ];
    }
}
