# Remove old files
rm -f build/packages/*.zip
rm -f build/*.zip

# Zip Component
cd administrator/components/com_queues
zip -qr ../../../build/packages/com_queues.zip ./*
cd ../../../

# Add language files to component zip
cd administrator/language/en-GB
zip -qur ../../../build/packages/com_queues.zip ./en-GB.com_queues.*
cd ../../../

# Zip Plugins
cd plugins/console/queue
zip -qr ../../../build/packages/plg_console_queue.zip ./*
cd ../../../

cd plugins/queue/default
zip -qr ../../../build/packages/plg_queue_default.zip ./*
cd ../../../

cd plugins/queue/queueexample
zip -qr ../../../build/packages/plg_queue_example.zip ./*
cd ../../../

# Zip Library
cd libraries/joomla-queues
zip -qr ../../build/packages/library_joomlaqueues.zip ./*
cd ../../

cd build

zip -q pkg_joomlaqueues.zip *.xml packages/*.zip

cd ../
