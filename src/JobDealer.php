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
         * @param int $priority
         * @param int $delay
         */
        public function add($text, $priority = Pheanstalk::DEFAULT_PRIORITY, $delay = Pheanstalk::DEFAULT_DELAY) {
            $this->pheanstalk->useTube($this->tube);
            $job_id = $this->pheanstalk->put($text, $priority, $delay);
            $this->logger->info(
                sprintf('Add job (%d): %s', $job_id, $text),
                compact('job_id', 'text', 'priority', 'delay')
            );
        }

        /**
         * Delete job
         *
         * @param int $jobId
         */
        public function done($jobId) {
            /** @var Job $job */
            $job = $this->pheanstalk->peek($jobId);
            $this->pheanstalk->delete($job);
            $this->logger->info(
                sprintf('Done job (%d): %s', $job->getId(), $job->getData()),
                ['job_id' => $job->getId(), 'text' => $job->getData()]
            );
        }

        /**
         * Delete given job and create new job with old or passed params
         *
         * @param int $jobId
         * @param int $priority
         * @param int $delay
         */
        public function reschedule($jobId, $priority = NULL, $delay = NULL) {
            /** @var Job $job */
            $job = $this->pheanstalk->peek($jobId);
            $jobStats = $this->pheanstalk->statsJob($job);
            $this->pheanstalk->useTube($this->tube);
            $priority = (NULL === $priority) ? $jobStats['pri'] : $priority;
            $delay    = (NULL === $delay) ? $jobStats['delay'] : $delay;
            $newJobId = $this->pheanstalk->put($job->getData(), $priority, $delay, $jobStats['ttr']);
            $this->pheanstalk->delete($job);

            $context = [
                'job_id'     => $newJobId,
                'old_job_id' => $jobId,
                'text'       => $job->getData(),
                'priority'   => $priority,
                'delay'      => $delay,
            ];
            $this->logger->info(sprintf('Reschedule job (%d => %d): %s', $jobId, $newJobId, $job->getData()), $context);
        }

    }
