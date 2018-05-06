# [WIP] Docker HostManager

[![Build Status](https://travis-ci.org/guillemcanal/docker-hostmanager.svg?branch=master)](https://travis-ci.org/guillemcanal/docker-hostmanager)
[![codecov](https://codecov.io/gh/guillemcanal/docker-hostmanager/branch/master/graph/badge.svg)](https://codecov.io/gh/guillemcanal/docker-hostmanager)
[![quality](https://sonarcloud.io/api/project_badges/measure?project=docker-hostmanager&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=docker-hostmanager)
[![smells](https://sonarcloud.io/api/project_badges/measure?project=docker-hostmanager&metric=code_smells)](https://sonarcloud.io/project/issues?id=docker-hostmanager&resolved=false&types=CODE_SMELL)

A Docker event listener that manage the `hosts` file on your host machine.

## Todo

- [x] Create a listener that listen to Docker container events
- [x] Create an hosts provider that use Traefik labels
- [x] Create an hosts file manager that manage the state of the `hosts` file
- [ ] Create a configurable command that run the Docker events listener
- [ ] Extract hostnames and update the hosts file at startup
- [ ] Generate self signed certificates at startup against a root CA
- [ ] Create a listener that generate self signed certificate against a root CA
- [ ] (Nice2Have) Create a command that list managed hosts
- [ ] (Nice2Have) Create an hosts provider that use Docker Aliases
- [ ] (Nice2Have) Create an hosts provider that an environment variable declared in the container
- [ ] (Nice2Have) Create a Docker Aliases hosts provider
