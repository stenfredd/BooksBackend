FROM elasticsearch:7.6.0

RUN bin/elasticsearch-plugin install https://github.com/fooger/elasticsearch-analysis-morphology/raw/master/analysis-morphology-7.6.0.zip