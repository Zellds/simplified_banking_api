#!/bin/bash

set -e

echo "Tearing down environment"
docker compose down --volumes --remove-orphans

echo "Cleaning images"
docker image prune -f

echo "Environment torn down"
