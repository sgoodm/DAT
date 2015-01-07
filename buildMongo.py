import struct
import sys
import csv
import simplejson as json
import numpy	
import pymongo
from decimal import *
 
# mongoimport --db Senegal --collection complete --type json --file /home/detuser/Desktop/aiddata_releases/Senegal/complete.json

files = {

	"projects" : '/home/detuser/Desktop/aiddata_releases/Senegal/projects.tsv',
	"locations" : '/home/detuser/Desktop/aiddata_releases/Senegal/locations.tsv',
	"transactions" : '/home/detuser/Desktop/aiddata_releases/Senegal/transactions.tsv',
	"ancillary" : '/home/detuser/Desktop/aiddata_releases/Senegal/ancillary.tsv',
	"complete" : '/home/detuser/Desktop/aiddata_releases/Senegal/complete.json',
	"readDelim" : '\t'
}

num_list = {
	"total_commitments",
	"total_disbursements",
	"transaction_value",
	"transaction_year"
}

sub_list = {
	"transactions"
}

with open (files["projects"], 'r') as projects:
	projectRead = csv.DictReader(projects, delimiter=files["readDelim"])

	with open (files["complete"], 'w') as writeJSON:

		for row in projectRead:

			# read in location, transaction and ancillary table
			# create new object for table
			# fill object with table contents

			with open (files["transactions"], 'r') as transactions:
				transactionRead = csv.DictReader(transactions, delimiter=files["readDelim"])

				row["transactions"] = {}


				for t_row in transactionRead:

					if row["project_id"] == t_row["project_id"]:

						row["transactions"][t_row["transaction_id"]] = {}

						for t_key in t_row.keys():

							row["transactions"][t_row["transaction_id"]][t_key] = t_row[t_key]



			# use Decimal on fields specified in num_list
	 		for key in row.keys():
				if key in num_list:
					row[key] = Decimal(row[key])
				elif key in sub_list:
					for sub in row[key]:
						for sub_key in row[key][sub].keys():
							if sub_key in num_list:
								row[key][sub][sub_key] = Decimal(row[key][sub][sub_key])

			# write row to json
			rowjson = json.dumps(row, ensure_ascii=True, use_decimal=True)
	 		writeJSON.write(rowjson+"\n")
