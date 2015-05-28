<?php

    namespace PersonalQueue;

    use Pheanstalk\Job;
    use Pheanstalk\PheanstalkInterface;
    use Psr\Log\LoggerInterface;

    class JobDealerTest extends \PHPUnit_Framework_TestCase {

        private $tube = 'test-tube';

        /**
         * @var JobDealer
         */
        private $sut;

        /**
         * @var PheanstalkInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        private $pheanstalkMock;

        /**
         * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        private $loggerMock;

        protected function setUp() {
            $this->pheanstalkMock = $this->getMock('Pheanstalk\\PheanstalkInterface');
            $this->loggerMock = $this->getMock('Psr\\Log\\LoggerInterface');
            $this->sut = new JobDealer;
            $this->sut
                ->setTube($this->tube)
                ->setPheanstalk($this->pheanstalkMock)
                ->setLogger($this->loggerMock);
        }

        public function testPeek() {
            $this->pheanstalkMock
                ->expects($this->atLeastOnce())
                ->method('peekReady')
                ->with($this->tube)
                ->willReturn('foo');

            $this->assertEquals('foo', $this->sut->peek());
        }

        public function testDone() {

            $jobId = 12345;

            $this->pheanstalkMock
                ->expects($this->once())
                ->method('delete')
                ->with($this->callback(function(Job $job) use ($jobId) {
                    return $job->getId() === $jobId;
                }))
            ;

            $this->pheanstalkMock
                ->expects($this->any())
                ->method('peek')
                ->with($jobId)
                ->willReturn(new Job($jobId, 'asdf'));

            $this->loggerMock
                ->expects($this->once())
                ->method('info')
                ->with('Done job (12345): asdf')
            ;

            $this->sut->done($jobId);

        }

    }
