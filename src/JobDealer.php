<?php

    namespace PersonalQueue;

    use Pheanstalk\Job;
    use Pheanstalk\PheanstalkInterface AS Pheanstalk;
    use Psr\Log\LoggerAwareInterface;
    use Psr\Log\LoggerInterface;

    class JobDealer implements LoggerAwareInterface {

        /**
         * @var Pheanstalk
         */
        protected $pheanstalk;

        /**
         * @var LoggerInterface
         */
        protected $logger;

        /**
         * @var string
         */
        protected $tube;

        /**
         * Sets a logger instance on the object
         *
         * @param LoggerInterface $logger
         * @return $this
         */
        public function setLogger(LoggerInterface $logger) {
            $this->logger = $logger;
            return $this;
        }

        /**
         * @param Pheanstalk $pheanstalk
         * @return $this
         */
        public function setPheanstalk(Pheanstalk $pheanstalk) {
            $this->pheanstalk = $pheanstalk;
            return $this;
        }

        /**
         * @param string $tube
         * @return $this
         */
        public function setTube($tube) {
            $this->tube = $tube;
            return $this;
        }

        /**
         * Get next ready job
         *
         * @return \Pheanstalk\Job
         * @throws \Pheanstalk\Exception
         */
        public function peek() {
            return $this->pheanstalk->peekReady($this->tube);
        }

        /**
         * Amount of ready jobs
         *
         * @return int
         */
        public function count() {
            return $this->pheanstalk->statsTube($this->tube)['current-jobs-ready'];
        }

        /**
         * Add Job
         *
         * @param string $text
         */
        public function add($text, $priority = Pheanstalk::DEFAULT_PRIORITY, $delay = Pheanstalk::DEFAULT_DELAY) {
            $this->pheanstalk->useTube($this->tube);
            $this->pheanstalk->put($text, $priority, $delay);
        }

        /**
         * Delete job
         *
         * @param int $jobId
         */
        public function done($jobId) {
            $this->pheanstalk->delete(new Job($jobId, []));
        }

        public function reschedule($jobId) {
            /** @var Job $job */
            $job = $this->pheanstalk->peek($jobId);
            $jobStats = $this->pheanstalk->statsJob($job);
            $this->pheanstalk->useTube($this->tube);
            $this->pheanstalk->put($job->getData(), $jobStats['pri'], $jobStats['delay'], $jobStats['ttr']);
            $this->pheanstalk->delete($job);
        }

    }