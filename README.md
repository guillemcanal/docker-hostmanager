# [WIP] Docker HostManager

A Docker event listener that manage the `hosts` file on your host machine.

## Todo

- [ ] Create a configurable command run the Docker events listener
- [x] Create a listener that listen to Docker container events
- [x] Create an hosts provider that use Traefik labels
- [x] Create an hosts file manager that manage the state of the `hosts` file
- [x] Unit/Functional tests
- [ ] (Nice2Have) Create a command that list managed hosts
- [ ] (Nice2Have) Create an hosts provider that use Docker Aliases
- [ ] (Nice2Have) Create an hosts provider that an environment variable declared in the container
- [ ] (Nice2Have) Create a Docker Aliases hosts provider
