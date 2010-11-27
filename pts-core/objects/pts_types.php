<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2010, Phoronix Media
	Copyright (C) 2010, Michael Larabel

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class pts_types
{
	public static function subsystem_targets()
	{
		return array("System", "Processor", "Disk", "Graphics", "Memory", "Network", "Other");
	}
	public static function software_license_types()
	{
		return array("Free", "Non-Free", "Retail", "Restricted");
	}
	public static function test_profile_state_types()
	{
		return array("PRIVATE", "BROKEN", "EXPERIMENTAL", "UNVERIFIED");
	}
	public static function identifiers_to_test_profile_objects($identifiers, $include_extensions = false, $remove_duplicates = true)
	{
		$test_profiles = array();

		foreach(pts_types::identifiers_to_objects($identifiers) as $object)
		{
			if($object instanceof pts_test_profile)
			{
				array_push($test_profiles, $object);
			}
			else if($object instanceof pts_test_suite)
			{
				foreach($object->get_contained_test_profiles() as $test_profile)
				{
					array_push($test_profiles, $test_profile);
				}
			}
			else if($object instanceof pts_result_file)
			{
				foreach($object->get_contained_test_profiles() as $test_profile)
				{
					array_push($test_profiles, $test_profile);
				}
			}
		}

		if($include_extensions)
		{
			$extended_test_profiles = array();

			for($i = 0; $i < count($test_profiles); $i++)
			{
				foreach(array_reverse($test_profiles[$i]->extended_test_profiles()) as $test_profile)
				{
					if(!in_array($test_profile, $extended_test_profiles))
					{
						array_push($extended_test_profiles, $test_profile);
					}
				}

				array_push($extended_test_profiles, $test_profiles[$i]);
			}

			// We end up doing this swapping around so the extended test profiles always end up before the tests extending them
			$test_profiles = $extended_test_profiles;
			unset($extended_test_profiles);
		}

		if($remove_duplicates)
		{
			$test_profiles = array_unique($test_profiles);
		}

		return $test_profiles;
	}
	public static function identifiers_to_objects($identifiers)
	{
		// Provide an array containing the location(s) of all test(s) for the supplied object name
		$objects = array();

		foreach(pts_arrays::to_array($identifiers) as $identifier_item)
		{
			if($identifier_item instanceof pts_test_profile || $identifier_item instanceof pts_test_suite || $identifier_item instanceof pts_result_file)
			{
				array_push($objects, $identifier_item);
			}
			else if(pts_test_profile::is_test_profile($identifier_item)) // Object is a test
			{
				array_push($objects, new pts_test_profile($identifier_item));
			}
			else if(pts_test_suite::is_suite($identifier_item)) // Object is suite
			{
				array_push($objects, new pts_test_suite($identifier_item));
			}
			else if(pts_result_file::is_test_result_file($identifier_item)) // Object is a saved results file
			{
				array_push($objects, new pts_result_file($identifier_item));
			}
			else if(pts_global::is_global_id($identifier_item)) // Object is a Phoronix Global file
			{
				// Clone it locally so it's just handled like a pts_result_file
				pts_global::clone_global_result($identifier_item);
				array_push($objects, new pts_result_file($identifier_item));
			}
			// add support for virtual suites: free, all, local, installed-tests
			// TODO XXX: Restore support for virtual suites
			/*
			else if(pts_is_virtual_suite($identifier_item))
			{
				foreach(pts_virtual_suite_tests($object) as $test)
				{
					foreach(pts_contained_tests($test, $include_extensions) as $sub_test)
					{
						array_push($objects, $sub_test);
					}
				}
			}*/
		}

		return $objects;
	}
	public static function identifier_to_object($identifier)
	{
		$return = pts_types::identifiers_to_objects($identifier);

		return isset($return[0]) ? $return[0] : false;
	}
	public static function is_result_file($identifier)
	{
		return pts_types::identifier_to_object($identifier) instanceof pts_result_file ? true : false;
	}
	public static function is_test_or_suite($identifier)
	{
		$object = pts_types::identifier_to_object($identifier);
		return $object instanceof pts_test_profile || $object instanceof pts_test_suite;
	}
}

?>
