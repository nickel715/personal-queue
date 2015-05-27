<?php

    namespace PersonalQueue;

    class JobDealerTest extends \PHPUnit_Framework_TestCase {

        private $tube = 'test-tube';

        /**
         * @var JobDealer
         */
        private $sut;

        /**
         * @var \Pheanstalk\PheanstalkInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        private $pheanstalk_mock;

        protected function setUp() {
            $this->pheanstalk_mock = $this->getMock('Pheanstalk\\PheanstalkInterface');
            $this->sut = new JobDealer;
            $this->sut
                ->setTube($this->tube)
                ->setPheanstalk($this->pheanstalk_mock);
        }

        public function testPeek() {
            $this->pheanstalk_mock
                ->expects($this->atLeastOnce())
                ->method('peekReady')
                ->with($this->tube)
                ->willReturn('foo');

            $this->assertEquals('foo', $this->sut->peek());
        }

    }
