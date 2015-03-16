import sys, os, time, copy, string
import csv, json
import shapefile
import numpy as np
from shapely.geometry import Point, shape, box

# T = time.time()

# load inputs and build file paths
country = sys.argv[1].lower()
adm = sys.argv[2]
output = sys.argv[3]

base = os.path.dirname(os.path.realpath(__file__))

file_in = str(base) + "/data/" + str(output) + ".csv"

file_out = base + '/data/'+output+'_geoagg.csv'


det_base = base[:base.rfind('/')]  + "/DET/resources"

continent_list = {
	"senegal":"africa",
	"timor-leste":"asia",
	"nepal":"asia",
	"uganda":"africa",
	"malawi":"africa"
}

shp_folder = det_base +"/"+ continent_list[country] +"/"+ country +"/shapefiles/ADM"+ adm
shp_list = [os.path.join(dirpath, f) for dirpath, dirnames, files in os.walk(shp_folder) for f in files if f.endswith('.shp')]
shp_file = shp_list[0]


# --------------------------------------------------


inFile = open(file_in, 'r')

if inFile.name.endswith(".csv"):
    delim = ','
elif inFile.name.endswith(".tsv"):
    delim = '\t'
else:
    sys.exit("Invalid File Extension")

locs = csv.DictReader(inFile, delimiter=delim)


shp_handle = shapefile.Reader(shp_file)

fields = shp_handle.fields[1:]
field_names = [field[0] for field in fields]
name_index = field_names.index("NAME_"+adm)

shape_names = []

for shp_record in shp_handle.shapeRecords():
	shape_names.append(shp_record.record[name_index])


shapes = shp_handle.shapes()

shpDict = {}

c = 0
for shp in shapes:

    shp_obj = shape(shp)

    minx, miny, maxx, maxy = shp_obj.bounds
    bounding_box = box(minx, miny, maxx, maxy)

    if c not in shpDict:
        shpDict[c] = {
        	"name":shape_names[c],
        	"total_commitments":0,
        	"total_disbursements":0,
        	"transaction_sum":0,
        	"project_count":0
        }

    for row in locs:

        try:
            lon = float(row['longitude'])
            lat = float(row['latitude'])

        except ValueError:
            lon = ''
            lat = ''

        if lon != '' and lat != '':

            curPoint = Point(lon, lat)

            if bounding_box.contains(curPoint):
                if curPoint.within(shp_obj):
                    shpDict[c]["total_commitments"] += float(row["total_commitments"]) / int(row["location_count"])
                    shpDict[c]["total_disbursements"] += float(row["total_disbursements"]) / int(row["location_count"])
                    shpDict[c]["transaction_sum"] += float(row["transaction_sum"]) / int(row["location_count"])
                    shpDict[c]["project_count"] += 1

    inFile.seek(0)
    c += 1




# generate csv output and write to file

outFile = open(file_out, 'w')

outFile.write("adm"+adm+"_name,total_commitments,total_disbursments,transaction_sum,project_count\n")

for cx in shpDict:
	shp_data = shpDict[cx]
	outFile.write(shp_data['name']+","+str(shp_data['total_commitments'])+","+str(shp_data['total_disbursements'])+","+str(shp_data['transaction_sum'])+","+str(shp_data['project_count'])+"\n")


# outFile.write(out_str)

# T = int(time.time() - T)
# print 'Total Runtime: ' + str(T//60) +'m '+ str(int(T%60)) +'s'
