# Name of the container to execute
TEST_CONTAINER = php-testing-polgen

# Location to mount inside the container
CONTAINER_MOUNT = /srv/app

# Location to mount from the host
CONTAINER_MOUNT_VOLUME = ${CURDIR}:${CONTAINER_MOUNT}

# Docker run parameters
DOCKER_PARAMS = --rm --volume ${CONTAINER_MOUNT_VOLUME}
DOCKER_RUN_CMD = docker run $(DOCKER_PARAMS)

.DEFAULT_GOAL := default

# Pass CLI params by default to container
.DEFAULT:
	docker build -t php-testing-polgen ./docker-testing
	$(DOCKER_RUN_CMD) $(TEST_CONTAINER) $@

build:
	docker build -t opapg .

start:
	docker build . -t opapg
	$(DOCKER_RUN_CMD) opapg from-openapi test.yaml --output=mine

stop:
	docker-compose down

opa:
	$(DOCKER_RUN_CMD) --entrypoint /opa opapg test /srv/app/mine.rego /srv/app/mine_test.rego -v
opa_coverage:
	$(DOCKER_RUN_CMD) --entrypoint /opa opapg test /srv/app/mine.rego /srv/app/mine_test.rego -v --coverage --format=json
opa2:
	$(DOCKER_RUN_CMD) --entrypoint /opa opapg version
restart: stop start
