#!/bin/sh

echo "INSERT INTO room (house, number) VALUES"
i=0
while [ $i -lt 300 ] ; do
  echo "(-1439014222, $i),"
  let i++
done
